<?php

use Fmw\Database;
use Fmw\Header;

require_once "sglobals.php";
global $application, $domain;
pagePermission($lgn = 1, $stff = 1, $njl = 0, $nhsp = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$mid = isset($_GET['mid']) ? mysql_num($_GET['mid']) : 0;
$days = isset($_POST['days']) ? mysql_num($_POST['days']) : 0;
$reason = isset($_POST['reason']) ? mysql_tex($_POST['reason']) : '';
$mafiosoID = isset($_POST['mafiosoID']) ? mysql_num($_POST['mafiosoID']) : 0;

switch ($action) {
    case 'fedjail':
        fedjail($application->db, $application->header, $application->user, $mid);
        break;
    case 'fedjaildo':
        fedjaildo($application->db, $application->header, $application->user, $days, $mafiosoID, $reason);
        break;
    case 'fedjailundo':
        fedjailundo($application->db, $application->header, $application->user, $mafiosoID);
        break;
    case 'gagform':
        gag_form($application->header, $application->user, $mid);
        break;
    case 'gagsub':
        gag_submit($application->db, $application->header, $application->user, $mafiosoID, $reason, $days);
        break;
    case 'ungagsub':
        ungag_submit($application->db, $application->header, $application->user);
        break;
    case 'ipform':
        ip_search_form($application->user);
        break;
    case 'ipsub':
        ip_search_submit($application->db, $application->user, $domain);
        break;
    default:
        print "Error: This script requires an action.";
        break;
}

function fedjail(Database $db, Header $headers, array $user, int $mid): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "<p>You are not staff and are not permitted here.</p><p><a href='home.php'>Head on home</a>.</p>";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Federal Jail</h3>
        <p>The mafioso will be put in federal jail and will be unable to do anything in the game - not even login. This is only for major transgressions.  The reason should be short, and should start with the rule violation. For example <em>(5) playing multiple mafioso.</em></p>
        <form action='staffPunish.php?action=fedjaildo' method='POST'>
    ";

    if ($mid > 0) {
        print mafioso($mid) . "<input type='hidden' name='mafiosoID' value={$mid}>";
    } else {
        print mafiosoMenu('mafiosoID');
    }

    print " 
            will be jailed
            <input type='text' size='5' name='days'> days for
            <input type='text' size='35' name='reason'><br><br>
            <input type='submit' value='Jail Mafioso'>
        </form>
        <br><br>
        <h5>Remove Jailed Player</h5>
        <p>This player will be taken out of the Federal Jail before their sentance is complete. Please make a note in their personal file about why you did this.</p>
        <form action='staffPunish.php?action=fedjailundo' method='POST'>
    ";

    if ($mid > 0) {
        print mafioso($mid) . "<input type='hidden' name='mafiosoID' value={$mid}>";
    } else {
        print "<select name='mafiosoID' type='dropdown'>";
        $query = $db->query("SELECT userid, username FROM users WHERE fedjail > 0 ORDER BY username");
        while ($row = mysqli_fetch_assoc($query)) {
            print "\n<option name='mafiosoID' value='{$row['userid']}'>{$row['username']}</option>";
        }

        print "\n</select>";
    }

    print "
            &nbsp;&nbsp;
            <input type='submit' value='Unjail Player'>
        </form>
    ";
}

function fedjaildo(Database $db, Header $headers, array $user, int $days, int $mafiosoID, string $reason): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "
            <p>You are not staff and are not permitted here.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Federally Jailing Mafioso</h3>
        <p>Player jailed.</p>
        <p><a href='staff.php'>Staff Home</a></p>
    ";

    $db->query("UPDATE users SET fedjail = {$days}, fedjailReason = '{$reason}', force_logout = 1 WHERE userid = {$mafiosoID}");

    logEvent($mafiosoID, "You are in Federal Jail for {$reason}.");
    staffLogAdd("Fedded ID {$mafiosoID} for {$days} days");
}

function fedjailundo(Database $db, Header $headers, array $user, int $mafiosoID): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "
            <p>You are not staff and are not permitted here.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Federally UN-Jailing Mafioso</h3>
        <p>Player un-jailed.</p>
        <p><a href='staff.php'>Staff Home</a></p>
    ";

    $db->query("UPDATE users SET fedjail = 0, fedjailReason = '' WHERE userid = {$mafiosoID}");

    logEvent($mafiosoID, "You are released from Federal Jail early for good behavior.");
    staffLogAdd("Un-Fedded ID {$mafiosoID}");
}

function gag_form(Header $headers, array $user, int $mid): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "
            <p>You are not staff and are not permitted here.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }
    print "
        <h3>Mafioso Gag Order</h3>
        <p>The mafioso will be unable to send mail, post in the forum or place anything in the newspaper. The reason should be short, and should start with the rule violation. For example <em>(3) disrespecting other mafioso.</em></p>
        <form action='staffPunish.php?action=gagsub' method='POST'>
    ";

    if ($mid > 0) {
        print mafioso($mid) . "<input type='hidden' name='mafiosoID' value={$mid}>";
    } else {
        print mafiosoMenu('mafiosoID');
    }

    print "
             will be gagged
             <input type='text' name='days' size='5'> hours for
             <input type='text' name='reason'><br><br>
             <input type='submit' value='Set Gag'>
         </form>
         <br><br>
         <h3>Remove Gag Order</h3>
         <p>The user will be allowed to communicate early. Please make a note in their file why you did this.</p>
         <form action='staffPunish.php?action=ungagsub' method='POST'>
            Player " . mafiosoMenu('user', "AND gagOrder > 0") . " &nbsp;&nbsp;
            <input type='submit' value='Remove Gag'>
        </form>
    ";
}

function gag_submit(Database $db, Header $headers, array $user, int $mafiosoID, string $reason, int $days): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "
            <p>You are not staff and are not permitted here.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET force_logout = 1, gagOrder = {$days}, gagReason = '{$reason}' WHERE userid = {$mafiosoID}");

    print "
        <h3>Mafioso Gag Order</h3>
        <p>Player silenced.</p>
        <p><a href='staff.php'>Staff Home</a></p>
    ";

    logEvent($mafiosoID, "You were banned from communications for {$days} hour(s) for the following reason: {$reason}");
    staffLogAdd("ID {$mafiosoID} has been Gagged for {$days} hours");
}

function ungag_submit(Database $db, Header $headers, array $user): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        print "
            <p>You are not staff and are not permitted here.</p>
            <p><a href='home.php'>Head on home</a>.</p>
        ";

        $headers->endpage();
        exit;
    }

    $userId = mysql_num($_POST['user']);
    $db->query("UPDATE users SET gagOrder = 0 WHERE userid = {$userId}");

    print "
        <h3>Mafioso Gag Order</h3>
        <p>" . mafioso($userId) . " gag order lifted.</p>
        <p><a href='staff.php'>Staff Home</a></p>.
    ";

    logEvent($userId, "You were unbanned from using mail. You can now use it again.");
    staffLogAdd("Gag order lifted for user ID {$userId}");
}

function ip_search_form(array $user): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>IP Search</h3>
        <form action='staffPunish.php?action=ipsub' method='post'>
            IP: <input type='text' name='ip' value='...' /><br />
            <input type='submit' value='Search' />
        </form>
    ";
}

function ip_search_submit(Database $db, array $user, string $domain): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        Searching for users with the IP: <b>{$_POST['ip']}</b><br />
        <table width=75%>
            <tr style='background:gray'>
                <th>User</th>
                <th>Level</th>
                <th>Money</th>
            </tr>
    ";

    $query = $db->query("SELECT userid, username, level, money FROM users WHERE trackActionIP = '{$_POST['ip']}'");
    $ids = array();
    while ($row = mysqli_fetch_assoc($query)) {
        $ids[] = $row['userid'];
        print "
            \n<tr>
                <td><a href='viewuser.php?u={$row['userid']}'>{$row['username']}</a></td>
                <td>{$row['level']}</td>
                <td>{$row['money']}</td>
            </tr>
        ";
    }

    print "
        </table><br />
        <b>Mass Jail</b><br />
        <form action='staffPunish.php?action=massjailip' method='post'>
            <input type='hidden' name='ids' value='" . implode(",", $ids) . "' /> Days: <input type='text' name='days' value='300' /> <br />
            Reason: <input type='text' name='reason' value='Same IP users, Mail fedjail@{$domain} with your case.' /><br />
            <input type='submit' value='Mass Jail' />
        </form>
    ";
}

$application->header->endPage();
