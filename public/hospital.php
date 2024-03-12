<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$userId = isset($_GET['uid']) ? mysql_num($_GET['uid']) : 0;

print '
    <h3>Hospital</h3>
    <div class=floatright>
        <br><br><br>
        <img src=\'assets/images/photos/surgery.jpg\' width=100 height=236 alt=Hospitalized>
';

if ($user['hospital'] > 0) {
    print '
        <p>
            &nbsp; <a href=\'raffle.php\'>Play the Raffle</a><br>
            &nbsp; <a href=\'family.php?action=list\'>Family Table</a><br>
            &nbsp; <a href=\'education.php\'>Look for a Mentor</a><br>
            &nbsp; <a href=\'job.php\'>Call your Boss</a><br>
            &nbsp; <a href=\'statistics.php\'>Statistics</a><br>
            &nbsp; <a href=\'statisticsTopMafia.php\'>Top Mafia</a><br>
            &nbsp; <a href=\'statisticsSpecial.php\'>Special Gear</a><br>
        </p>
';
}

print '</div><br>';

switch ($action) {
    case "laugh":
        laugh($db, $headers, $user, $userId);
        break;
    case "flowers":
        send_flowers($db, $headers, $user, $userId);
        break;
    default:
        show_list($db, $user);
        break;
}

function laugh(Database $db, Header $headers, array $user, int $userId): void
{
    if ($userId == null) {
        print 'UserId is missing';

        $headers->endPage();
        exit;
    }

    $row = mysqli_fetch_assoc($db->query("SELECT level, hospital, userid FROM users WHERE userid = {$userId}"));
    $cost = $row['hospital'] * 3929;
    if ($row['hospital'] < 1) {
        print '
            <p>I don\'t know who you are trying to laugh at, but they\'re not here.</p>
            <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'home.php\'>head on home</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    if ($user['money'] < $cost) {
        print '
            <p>Did you not read the small print?  It costs ' . moneyFormatter($cost) . ' in bribes to get into their room and laugh at them. If you wait a while, it will get cheaper as they get healthier and move to a room closer to the entrance.</p>
            <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'bank.php\'>head to the bank</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    print '
        <p>You spent ' . moneyFormatter($cost) . ' to bribe your way to see ' . mafioso($userId) . '. You take a deep breath, point at them, and laugh visciously. The mental anguish increases their their hospital time by a whole minute. Jerk.</p>
        <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'explore.php\'>head into town</a></p>
    ';

    $db->query("UPDATE users SET money = money - {$cost} WHERE userid = {$user['userid']}");
    $db->query("UPDATE users SET hospital = hospital + 1 WHERE userid = {$userId}");

    logEvent($userId, mafiosoLight($user['userid']) . " laughed at you in the hospital.");
}

function send_flowers(Database $db, Header $headers, array $user, int $userId): void
{
    if ($userId == 0) {
        print 'UserId is missing';

        $headers->endPage();
        exit;
    }

    $row = mysqli_fetch_assoc($db->query("SELECT level, hospital, userid FROM users WHERE userid = {$userId}"));
    $cost = $row['hospital'] * 3929;
    if ($row['hospital'] < 1) {
        print '
            <p>I don\'t know who you are trying to buy flowers for, but they\'re not here.</p>
            <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'home.php\'>head on home</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    if ($user['money'] < $cost) {
        print '
            <p>Did you not read the small print?  It costs ' . moneyFormatter($cost) . ' to buy flowers for ' . mafioso($userId) . '.  Apparently flowers are <strong>really</strong> expensive here! If you wait a while, it will get cheaper as they get healthier and move to a better room.</p>
            <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'bank.php\'>head to the bank</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    print '
        <p>You spent ' . moneyFormatter($cost) . ' to purchase flowers for ' . mafioso($userId) . '. The pleasant aroma reduces their hospital time by a whole minute. Nice.</p>
        <p><a href=\'hospital.php\'>Back to the hospital</a> or <a href=\'explore.php\'>head into town</a></p>
    ';

    $db->query("UPDATE users SET money = money - {$cost} WHERE userid = {$user['userid']}");
    $db->query("UPDATE users SET hospital = hospital - 1 WHERE userid = {$userId}");

    logEvent($userId, mafiosoLight($user['userid']) . " sent you flowers in the hospital.");
}

function show_list(Database $db, array $user): void
{
    print '
        <table width=80% class=table cellspacing=0 cellpadding=2 style=\'font-size:smaller;\'>
            <tr>
                <th style=\'text-align:left;\'>Mafioso</th>
                <th style=\'text-align:left;\'>Reason</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
    ';

    $result = $db->query("SELECT hospital, userid, hjReason FROM users WHERE hospital > 0 ORDER BY hospital DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $cost = ($row['hospital'] * 3929);
        print '
            <tr>
                <td>' . mafioso($row['userid']) . '</td>
                <td>' . $row['hjReason'] . '</td>
                <td class=center>' . $row['hospital'] . '</td>
                <td class=center>
        ';

        if ($user['hospital'] == 0) {
            print '
                <a title=\'' . $cost . '\' href=\'hospital.php?action=flowers&uid=' . $row['userid'] . '\'>flower</a> &nbsp;&middot;&nbsp; 
                <a title=\'' . $cost . '\' href=\'hospital.php?action=laugh&uid=' . $row['userid'] . '\'>laugh</a>
            ';
        }

        print '
                </td>
            </tr>
        ';
    }

    print '</table>';
}

$headers->endPage();
