<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$act = mysql_tex($_GET['act']);
$amt = mysql_num($_GET['amt']);
$use = mysql_tex($_GET['use']);
$mid = mysql_num($_GET['mid']);

switch ($act) {
    case "genergy":
        gainEnergy($db, $headers, $user, $userId);
        break;
    case "prestig":
        prestige($db, $headers, $user, $userId);
        break;
    case "disresp":
        disrespect($db, $user, $userId, $mid);
        break;
    case "respect":
        respect($db, $headers, $user, $userId, $mid);
        break;
    case "sendwea":
        sendWealth($db, $headers, $user, $userId, $amt, $mid, $use);
        break;
}

function disrespect(Database $db, array $user, int $userId, int $mid): void
{
    if ($user['respectCut'] == 0) {
        print '
            <h3>Respect</h3>
            <p>You disrespected ' . mafiosoLight($mid) . '. They lose one token of respect due to your snide comments and backstabbing.</p>
            <p>Remember you may only do this once a day so only disrespect those who truly deserve it.</p>
        ';

        $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$mid}");
        $db->query("UPDATE users SET respectCut = respectCut + 1 WHERE userid = {$userId}");

        logEvent($mid, 'Someone disrespected you reducing your Respect by one.');
    } else {
        print '
            <h3>Respect</h3>
            <p>You have already disrespected someone today. You can only do this once a day so you will have to wait until after midnight to do it again.</p>
        ';
    }
}

function gainEnergy(Database $db, Header $headers, array $user, int $userId): void
{
    if ($user['respect'] < 1 || $user['energy'] >= $user['maxenergy'] - 5) {
        print '
            <h3>Respect for Energy</h3>
            <p>Either you do not have enough respect or you already have the most energy you can have.  Either way you cannot do this.</p>
        ';

        $headers->endpage();
        exit;
    }

    $egain = min(round($user['maxenergy'] / 2), ($user['maxenergy'] - $user['energy']));
    $db->query("UPDATE users SET energy = energy + {$egain}, respect = respect - 1 WHERE userid = {$userId}");

    print '
        <h3>Respect for Energy</h3>
        <p>You have increased your energy by 50% up to your current maximum.</p>
    ';
}

function prestige(Database $db, Header $headers, array $user, int $userId): void
{
    if ($user['respect'] < 1) {
        print '
            <h3>Respect for IQ</h3>
            <p>You do not have enough respect to do what you are trying to do.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$userId}");
    $db->query("UPDATE userstats SET IQ = IQ + 25 WHERE userid = {$userId}");

    print '
        <h3>Respect for IQ</h3>
        <p>You have gained 25 IQ</p>
    ';
}

function respect(Database $db, Header $headers, array $user, int $userId, int $mid): void
{
    if ($userId == $mid) {
        print '
            <h3>Respect</h3>
            <p>You spend some time telling everyone how wonderful you are, and it backfires! You lose a Token of Respect due to your blowhard nature. Clearly, you cannot respect yourself.</p>
        ';

        $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$userId}");

        $headers->endpage();
        exit;
    }

    if ($user['respectGift'] < 2) {
        print '
            <h3>Respect</h3>
            <p>You have shown ' . mafiosoLight($mid) . ' proper honor and respect and told the neighborhood of their excellent nature. They gain a token of respect due to your actions.</p>
            <p>Remember you may only do this twice a day so only show respect to those who truly deserve it.</p>
        ';

        $db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$mid}");
        $db->query("UPDATE users SET respectGift = respectGift + 1 WHERE userid = {$userId}");

        logEvent($mid, 'Someone respected you and increased your Respect by one.');
    } else {
        print '
            <h3>Respect</h3>
            <p>You have already shown proper respect to two people today. You must wait until after midnight to help others in this way.</p>
        ';
    }
}

function sendWealth(Database $db, Header $headers, array $user, int $userId, int $amt, int $mid, string $use): void
{
    switch ($use) {
        case 'respect' :
            $ld = '';
            $title = 'tokens of respect';
            $type = 'tokens';
            break;
        case 'money' :
            $ld = '$';
            $title = 'in cash';
            $type = 'cash';
            break;
        case 'moneyChecking' :
            $ld = '$';
            $title = 'from checking';
            $type = 'bank';
            break;
    }

    if ($amt > $user[$use]) {
        print '
            <h3>Transfer Failed</h3>
            <p>You do not have enough to make that transaction work. Clearly, your desire is greater than your ability.</p>
            <p><a href=\'home.php\'>Head on home</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($amt > 0) {
        $db->query("UPDATE users SET {$use} = {$use} - {$amt} WHERE userid = {$userId}");
        $db->query("UPDATE users SET {$use} = {$use} + {$amt} WHERE userid = {$mid}");

        print '
            <h3>Transfer Wealth</h3>
            <p>You sent ' . $ld . number_format($amt) . ' ' . $title . ' to ' . mafioso($mid) . ' and an event was sent to notify them.</p>
            <p><a href=\'home.php\'>Head on home</a></p>
        ';

        logEvent($mid, "You received {$ld}" . number_format($amt) . " {$title} from " . mafiosoLight($userId) . ".");

        $rip = mysqli_fetch_assoc($db->query("SELECT trackActionIP FROM users WHERE userid = {$mid}"));
        logWealth($userId, $user['trackActionIP'], $mid, $rip['trackActionIP'], $amt, "{$type}", 'transfer');

        $headers->endpage();
        exit;
    }

    print '
        <h3>Transfer Wealth</h3>
        <p>You are transferring ' . $title . ' and have ' . $ld . number_format($user[$use]) . ' available.</p>
        <form action=\'wealth.php?act=sendwea\' method=GET>
            <input type=hidden name=act value=\'sendwea\'>
            <input type=hidden name=use value=\'' . $use . '\'>
            <table cellspacing=0 cellpadding=3 class=table>
                <tr><td>Recipient: </td>
    ';

    if ($mid > 0) {
        print '<td>' . mafioso($mid) . '<input type=hidden name=mid value=' . $mid . '></td>';
    } else {
        print '<td>' . mafiosoMenu('mid') . '</td>';
    }
    print '
        </tr>
        <tr>
            <td>Amount: </td>
            <td>' . $ld . '<input type=text name=amt size=15 value=\'1\'></td>
        </tr>
        <tr><td colspan=2 class=center><input type=submit value=\'Send the Loot\'><br><br><br></td></tr>
    ';

    if ($mid > 0) {
        $query = $db->query("SELECT lwTime, lwReceiver, lwAmount, lwType FROM logsWealth WHERE lwSender = {$userId} AND lwReceiver = {$mid} ORDER BY lwTime DESC LIMIT 15");
        print '
            <tr><td colspan=2><h5>Past Transactions with ' . mafiosoname($mid) . '</h5></td></tr>
            <tr><td style=\'text-align:left;\'>Time</td> <td style=\'text-align:right;\'>Amount</td></tr>
        ';

        while ($row = mysqli_fetch_assoc($query)) {
            switch ($row['lwType']) {
                case "tokens" :
                    $ld = '';
                    $title = 'respect';
                    break;
                case "cash" :
                    $ld = '$';
                    $title = 'in cash';
                    break;
                case "bank" :
                    $ld = '$';
                    $title = ' checks';
                    break;
            }
            print '
                <tr>
                    <td>' . date("F j, Y, g:i a", $row['lwTime']) . '</td>
                    <td style=\'text-align:right;\'>' . $ld . number_format($row['lwAmount']) . ' ' . $title . '</td>
                </tr>
            ';
        }
    }

    print '</table>';
}

$headers->endpage();
