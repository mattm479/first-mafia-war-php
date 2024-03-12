<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$getID = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$spend = isset($_GET['spend']) ? mysql_tex($_GET['spend']) : '';

switch ($action) {
    case "rescut":
        respect_cut($db, $user, $userId, $getID);
        break;
    case "resgift":
        respect_gift($db, $headers, $user, $userId, $getID);
        break;
    default:
        use_respect($db, $headers, $user, $userId, $spend);
        break;
}

function respect_cut(Database $db, array $user, int $userId, int $getID): void
{
    if ($user['respectCut'] == 0) {
        print "
            <h3>Respect</h3>
            <p>You have disrespected " . mafiosoLight($getID) . ". They lose one point of respect due to your comments.</p>
            <p>Remember you may only do this once a day so only show disrespect those who truly deserve it.</p>
        ";

        $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$getID}");
        $db->query("UPDATE users SET respectCut = respectCut + 1 WHERE userid = {$userId}");

        logEvent($getID, 'Someone disrespected you reducing your Respect by one.');
    } else {
        print "
            <h3>Respect</h3>
            <p>You have already disrespected someone today.</p>
        ";
    }
}

function respect_gift(Database $db, Header $headers, array $user, int $userId, int $getID): void
{
    if ($userId == $getID) {
        print "
            <h3>Grant Respect</h3>
            <p>You cannot benefit yourself.</p>
            <p><a href='home.php'>Home</a></p>
        ";

        $headers->endpage();
        exit;
    }

    if ($user['respectGift'] < 2) {
        print "
            <h3>Respect</h3>
            <p>You have shown " . mafiosoLight($getID) . " proper respect. They gain one point of respect because you honored them.</p>
            <p>Remember you may only do this twice a day so only show respect to those who truly deserve it.</p>
        ";

        $db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$getID}");
        $db->query("UPDATE users SET respectGift = respectGift + 1 WHERE userid = {$userId}");

        logEvent($getID, 'Someone respected you and increased your Respect by one.');
    } else {
        print "
            <h3>Respect</h3>
            <p>You have already respected two people today.</p>
        ";
    }
}

function use_respect(Database $db, Header $headers, array $user, int $userId, string $spend): void
{
    if (!$spend) {
        print "
            <h3>Tokens of Respect</h3>
            <p>You have <strong>{$user['respect']}</strong> hard earned tokens of respect. Would you like to use some of that respect now? Each action requires just one token of respect.</p>
            <a href='respect.php?spend=refill'>Boost Energy</a> &nbsp;&middot;&nbsp;
            <a href='respect.php?spend=IQ'>Improve IQ</a><br>
        ";
    } else {
        if ($user['respect'] < 1) {
            print "
                <h3>Tokens of Respect</h3>
                <p>You don't have enough respect.</p>
            ";

            $headers->endpage();
            exit;
        }
        if ($spend == 'refill') {
            if ($user['energy'] == $user['maxenergy']) {
                print "
                    <h3>Tokens of Respect</h3>
                    <p>You already have full energy.</p>
                ";

                $headers->endpage();
                exit;
            }

            $energygain = round($user['maxenergy'] / 2);

            $db->query("UPDATE users SET energy = energy + {$energygain}, respect = respect - 1 WHERE userid = {$userId}");
            $db->query("UPDATE users SET energy = maxenergy WHERE userid = {$userId} and energy > maxenergy");

            print "
                <h3>Tokens of Respect</h3>
                <p>You have increased your energy by 50% up to your current maximum.</p>
            ";
        } else if ($spend == 'IQ') {
            $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$userId}");
            $db->query("UPDATE userstats SET IQ = IQ + 20 WHERE userid = {$userId}");

            print "
                <h3>Tokens of Respect</h3>
                <p>You have gained 20 IQ.</p>
            ";
        }
    }
}

$headers->endpage();
