<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=1, $nlck=0);

$action     = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$userId     = isset($_GET['uid']) ? mysql_num($_GET['uid']) : 0;
$respect    = isset($_POST['res']) ? mysql_num($_POST['res']) : 0;

print '
    <h3>County Jail</h3>
    <div class=floatright>
        <br><br><br><img src=\'assets/images/photos/prison.jpg\' width=100 height=236 alt=Jailed>
';

if ($user['jail'] > 0) {
    print '
        <p>
            &nbsp; <a href=\'family.php?action=list\'>Family Table</a><br>
            &nbsp; <a href=\'raffle.php\'>Play the Raffle</a><br>
            &nbsp; <a href=\'education.php\'>Look for a Mentor</a><br>
            &nbsp; <a href=\'gym.php\'>Work out</a><br>
            &nbsp; <a href=\'statistics.php\'>Statistics</a><br>
            &nbsp; <a href=\'statisticsTopMafia.php\'>Top Mafia</a><br>
            &nbsp; <a href=\'statisticsSpecial.php\'>Special Gear</a><br>
        </p>
    ';
}

print '</div><br>';

switch ($action) {
    case "bribe":
        bribe($db, $headers, $user, $userId);
        break;
    case "bust":
        bust($userId);
        break;
    case "bustdo":
        do_bust($db, $headers, $user, $userId, $respect);
        break;
    default:
        show_list($db, $user);
        break;
}

function bribe(Database $db, Header $headers, array $user, int $userId): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT level, jail, username, userid FROM users WHERE userid = {$userId}"));
    $cost = ($row['level'] * 225 * $row['jail']);
    if (!$userId || $row['jail'] < 1) {
        print '
            <p>I don\'t know who you are trying to get out of jail, but they\'re not here.</p>
            <p><a href=\'jail.php\'>Back to jail</a> or <a href=\'home.php\'>head on home</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    if ($user['money'] < $cost) {
        print '
            <p>Did you not read the small print?  It costs ' . moneyFormatter($cost) . ' to even have a chance of getting ' . mafioso($userId) . ' out of jail today. If you wait a while, it will get cheaper as they serve more time.</p>
            <p><a href=\'jail.php\'>Back to jail</a> or <a href=\'bank.php\'>head to the bank</a>.</p>
        ';

        $headers->endPage();
        exit;
    }

    if (rand(1, 10) == 10) {
        print '
            <p>While trying to bribe the guards, the warden spotted you and arrests you on the spot. Naturally he pocketed the bribe money as well.</p>
            <p><a href=\'jail.php\'>Go to jail</a></p>
        ';

        $time = rand(2, 5);
        $db->query("UPDATE users SET jail = jail + {$time}, hjReason = 'Caught bribing the guards for {$row['username']}' WHERE userid = {$user['userid']}");
    } else {
        print '
            <p>You have successfully sprung ' . mafioso($userId) . ' out of jail by bribing the guards!</p>
            <p><a href=\'jail.php\'>Back to jail</a> or <a href=\'explore.php\'>head into town</a></p>
        ';

        $db->query("UPDATE users SET money = money - {$cost}, jailBails = jailBails + 1 WHERE userid = {$user['userid']}");
        $db->query("UPDATE users SET jail = 0 WHERE userid = {$userId}");

        logEvent($userId, mafiosoLight($user['userid'])." sprung you from jail by bribing the guards.");
    }
}

function bust(int $userId): void
{
    print '
        <p>Breaking someone out of jail is risky business and takes a Mafioso of some status to pull it off. You are breaking ' . mafioso($userId) . ' out of jail.</p>
        <p>How much Respect are you willing to sacrifice to increase your chance of success? You might succeed with only 1 and can spend up to 5. What do you do?</p>
        <form action=\'jail.php?action=bustdo&uid=' . $userId . '\' method=POST>
            <select name=res>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
            </select>
            <input type=submit value=\'Give Respect\'>
        </form>
    ';
}

function do_bust(Database $db, Header $headers, array $user, int $userId, int $respect): void
{
    $row = mysqli_fetch_assoc($db->query("SELECT userid, jail, crimeLevel, level, username FROM users WHERE userid = {$userId}"));

    if ($user['jail'] && $user['userid'] != $userId) {
        print '
            <p>You cannot bail out other people while you yourself are in jail. Bust yourself out first!</p>
            <p><a href=\'jail.php\'>Back to Jail</a></p>
        ';

        $headers->endPage();
        exit;
    }

    if (!$userId || $row['jail'] < 1) {
        print '
            <p>I don\'t know who you are trying to bust out of jail, but they\'re not here.</p>
            <p><a href=\'jail.php\'>Back to Jail</a></p>
        ';

        $headers->endPage();
        exit;
    }

    if ($user['respect'] < $respect) {
        print '
            <p>You do not have enough respect to get anyone out of jail today.</p>
            <p><a href=\'jail.php\'>Back to Jail</a></p>
        ';

        $headers->endPage();
        exit;
    }

    $rb = mysqli_fetch_assoc($db->query("SELECT userid FROM coursesdone WHERE userid = {$user['userid']} AND courseid = 25"));
    if (isset($rb['userid']) && $rb['userid'] == $user['userid']) {
        $respect *= 2;
    }

    $bustFormula = (((max(8, ($user['level'] / 9))) * $respect) / ($row['crimeLevel'] * $row['jail'])) * 100;
    $chance = max(15, min($bustFormula,95));
    if ($user['userid'] == $userId) {
        $chance = max(25, min($bustFormula,95));
    }

    if (rand(1, 100) < $chance) {
        $gain = $row['level'] * 5;
        print '
            <p>You successfully broke ' . mafioso($userId) . ' out of jail!</p>
            <p><a href=\'jail.php\'>Back</a> or <a href=\'explore.php\'>head into town</a></p>
        ';

        $rc = mysqli_fetch_assoc($db->query("SELECT userid FROM coursesdone WHERE userid = {$user['userid']} AND courseid = 35"));
        if (isset($rc['userid']) && $rc['userid'] == $user['userid']) {
            $respect--;
            if ($respect <= 0) {
                $respect = 0;
            }
        }

        $db->query("UPDATE users SET exp = exp + {$gain}, respect = respect - {$respect}, jailBusts = jailBusts + 1 WHERE userid = {$user['userid']}");
        $db->query("UPDATE users SET jail = 0 WHERE userid = {$userId}");

        logEvent($userId, mafiosoLight($user['userid'])." busted you out of jail at great personal risk.");
    } else {
        print '
            <p>While trying to bust out your friend, a guard spotted you and dragged you into jail yourself. Unlucky!</p>
            <p>You should have shown more respect.</p>
            <p><a href=\'jail.php\'>Go to jail</a></p>
        ';

        $time = ($chance);
        $db->query("UPDATE users SET jail = jail + {$time}, hjReason = 'Caught trying to bust out {$row['username']}', respect = respect - {$respect}, jailBusts = jailBusts + 1 WHERE userid = {$user['userid']}");
    }
}

function show_list(Database $db, array $user): void
{
    print '
        <table width=80% class=table cellpadding=2 style=\'font-size:smaller;\'>
            <tr><th style=\'text-align:left;\'>Mafioso</th><th style=\'text-align:left;\'>Reason</th><th>Time</th><th>Actions</th></tr>
    ';

    $result = $db->query("SELECT userid, level, jail, hjReason FROM users WHERE jail > 0 ORDER BY jail DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $cost = moneyFormatter($row['level'] * 225 * $row['jail']);
        print '
            <tr>
                <td>' . mafioso($row['userid']) . '</td>
                <td>' . $row['hjReason'] . '</td>
                <td class=center>' . $row['jail'] . '</td>
                <td style=\'text-align:center; font-size:smaller;\'><a title=\'costs respect!\' href=\'jail.php?action=bust&uid=' . $row['userid'] . '\'>bust</a> &nbsp;&middot;&nbsp;
        ';

        if ($user['jail']) {
            print '<a href=\'attack.php?ID=' . $row['userid'] . '\'>attack</a>';
        } else {
            print '<a title=\'' . $cost . '\' href=\'jail.php?action=bribe&uid=' . $row['userid'] . '\'>bribe</a>';
        }

        print '
                </td>
            </tr>
        ';
    }

    print '</table>';
}

$headers->endPage();
