<?php

use Fmw\Database;
use Fmw\Header;

require_once "sglobals.php";
global $application;
pagePermission($lgn = 1, $stff = 1, $njl = 0, $nhsp = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$ips = isset($_GET['ips']) ? mysql_tex($_GET['ips']) : '';
$mid = isset($_GET['mid']) ? mysql_num($_GET['mid']) : 0;
$csh = isset($_POST['csh']) ? mysql_num($_POST['csh']) : 0;
$itm = isset($_POST['itm']) ? mysql_num($_POST['itm']) : 0;
$grp = isset($_POST['grp']) ? mysql_tex($_POST['grp']) : '';
$rsp = isset($_POST['rsp']) ? mysql_num($_POST['rsp']) : 0;
$txt = isset($_POST['txt']) ? mysql_tex($_POST['txt']) : '';
$tx2 = isset($_POST['tx2']) ? mysql_tex($_POST['tx2']) : '';
$uid = isset($_POST['uid']) ? mysql_num($_POST['uid']) : 0;
$yno = isset($_POST['yno']) ? mysql_tex($_POST['yno']) : '';

switch ($action) {
    case 'accapprove':
        account_approve($application->db, $application->user, $mid);
        break;
    case 'accflogout':
        account_logout($application->db, $application->user, $mid);
        break;
    case 'accsuspend':
        account_suspend($application->db, $application->user, $mid);
        break;
    case 'accdeleted':
        account_delete($application->db, $application->header, $application->user, $uid, $yno);
        break;
    case 'grpgivform':
        group_giving_form($application->db, $application->user);
        break;
    case 'grpgivsubm':
        group_giving_submit($application->db, $application->user, $csh, $grp, $itm, $rsp, $txt);
        break;
    case 'indgivform':
        individual_giving_form($application->db, $application->user);
        break;
    case 'indgivsubm':
        individual_giving_submit($application->db, $application->user, $csh, $itm, $rsp, $txt, $uid);
        break;
    case 'ipsrchform':
        ip_search_form($application->db, $application->user);
        break;
    case 'mafiosofrm':
        mafioso_form($application->db, $application->user, $uid);
        break;
    case 'mafiososub':
        mafioso_sub($application->db, $application->user, $txt, $tx2, $uid);
        break;
    case 'edituser':
        edit_user_begin($application->user);
        break;
    case 'edituserform':
        edit_user_form($application->db, $application->user);
        break;
    case 'editusersub':
        edit_user_sub($application->db, $application->header, $application->user);
        break;
    case 'watchfuleye':
        watchful_eye($application->db, $application->user);
        break;
    case 'begin_watch':
        watchful_eye_begin($application->db, $application->user, $mid);
        break;
    case 'end_watchin':
        watchful_eye_end($application->db, $application->user, $mid);
        break;
    case 'ipsrchsubm':
    default:
        ip_search_submit($application->db, $application->user, $ips);
        break;
}

function account_approve(Database $db, array $user, int $mid): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Account Renewed</h3><br>
        <p>You have re-activated the account of " . mafioso($mid) . ". They will receive an email letting them know they may login at any time..</p>
    ";

    $db->query("UPDATE users SET login_name = username WHERE userid = {$mid}");

    $ru = mysqli_fetch_assoc($db->query("SELECT email FROM users WHERE userid = {$mid}"));
    $email = $ru['email'];
    $subject = "First Mafia War Account Activated";
    $body = "Your account has been activated and you may login at any time. Enjoy the game!\n\n-Kef";
    $headers = "From: kefern@firstmafiawar.com\r\n";
    if (mail($email, $subject, $body, $headers)) {
        staffLogAdd(mafiosoLight($mid) . " has been approved.");
    } else {
        staffLogAdd(mafiosoLight($mid) . " was approved, but the email failed.");
    }
}

function account_delete(Database $db, Header $headers, array $user, int $uid, string $yno): void
{
    if ($user['userid'] != 1) {
        unauthorized($user['userid'], 2);
    }

    $step = isset($_GET['step']) ? mysql_num($_GET['step']) : 0;
    switch ($step) {
        default:
            print "
                <h3>Delete User</h3><br>
                <p>Please be careful. This is NOT reversible. EVER.</p>
                <form action='staffUsers.php?action=accdeleted&step=2' method='POST'>
                    User ID: <input type='text' name='uid'> &nbsp;
                    <input type='submit' value='Delete User'>
                </form>
            ";
            break;
        case 2:
            print "
                <h3>Confirm</h3><br>
                <p>Please confirm you want to delete " . mafioso($uid) . ". There is no going back.</p>
                <form action='staffUsers.php?action=accdeleted&step=3' method='POST'>
                    <input type='hidden' name='uid' value='{$uid}'>
                    <input type='submit' name='yno' value='Yes'>
                    <input type='submit' name='yno' value='No'>
                </form>
            ";
            break;
        case 3:
            if ($yno == 'No') {
                print "
                    <p>User not deleted.</p>
                    <p><a href='staff.php'>Back to staff</a>
                ";

                $headers->endpage();
                exit;
            }

            if ($yno == 'Yes') {
                $username = mafiosoName($uid);

                $db->query("DELETE FROM conMarket WHERE cmConsignor = '{$uid}'");
                $db->query("DELETE FROM coursesdone WHERE userid = '{$uid}'");
                $db->query("DELETE FROM forumPosts WHERE fpMafioso = '{$uid}'");
                $db->query("DELETE FROM forumTopics WHERE ftMafioso = '{$uid}'");
                $db->query("DELETE FROM inventory WHERE inv_userid = '{$uid}'");
                $db->query("DELETE FROM logsAttacks WHERE laAttacker = '{$uid}'");
                $db->query("DELETE FROM logsAttacks WHERE laDefender = '{$uid}'");
                $db->query("DELETE FROM logsDonations WHERE ldBuyer = '{$uid}'");
                $db->query("DELETE FROM logsEvents WHERE leUser = '{$uid}'");
                $db->query("DELETE FROM logsItems WHERE liSender = '{$uid}'");
                $db->query("DELETE FROM logsItems WHERE liReceiver = '{$uid}'");
                $db->query("DELETE FROM logsWealth WHERE lwSender = '{$uid}'");
                $db->query("DELETE FROM logsWealth WHERE lwReceiver = '{$uid}'");
                $db->query("DELETE FROM mail WHERE mail_to = '{$uid}'");
                $db->query("DELETE FROM mail WHERE mail_from = '{$uid}'");
                $db->query("DELETE FROM news WHERE newsFrom = '{$uid}'");
                $db->query("DELETE FROM referals WHERE refREFER = '{$uid}'");
                $db->query("DELETE FROM referals WHERE refREFED = '{$uid}'");
                $db->query("DELETE FROM stafflog WHERE user = '{$uid}'");
                $db->query("DELETE FROM users WHERE userid = '{$uid}'");
                $db->query("DELETE FROM userstats WHERE userid = '{$uid}'");

                print "
                    <p>The user has been ended.</p>
                    <p><a href='staff.php'>Staff home</a></p>
                ";

                staffLogAdd("{$username} has been utterly destroyed");
            }
            break;
    }
}

function account_logout(Database $db, array $user, int $mid): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        unauthorized($user['userid'], 1);
    }

    $db->query("UPDATE users SET force_logout = 1 WHERE userid = {$mid}");

    print "
        <h3>Forced Logout</h3><br>
        <p>" . mafioso($mid) . " will be logged out on their next action. They will be able to login normally depending on any other actions.</p>
        <p><a href='staff.php'>Staff Home</a></p>
    ";

    staffLogAdd("Forced " . mafiosoLight($mid) . " to logout");
}

function account_suspend(Database $db, array $user, int $mid): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Account Suspended</h3><br>
        <p>You have suspended the account of " . mafioso($mid) . ". Their account can be re-activated at any time from thier profile page. However, suspensions are serious things, so Kef should approve the activation.</p>
    ";

    $db->query("UPDATE users SET login_name = '', force_logout = 1 WHERE userid = {$mid}");

    staffLogAdd("Suspended the account of " . mafiosoLight($mid) . ".");
}

function edit_user_begin(array $user): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Editing Player</h3><br>
        <p>Please take care when editing a player. Much of this is not easily reversed.</p>
        <form action='staffUsers.php?action=edituserform' method='post'>
            Player: " . mafiosoMenu('user') . " &nbsp;&nbsp;
            <input type='submit' value='Edit Player'>
        </form>
        <br><br>
        Enter Player ID if you prefer:
        <form action='staffUsers.php?action=edituserform' method='post'>
            Player ID: <input type='text' name='user' size='5' value='0'> &nbsp;&nbsp;
            <input type='submit' value='Edit Player'>
        </form>
    ";
}

function edit_user_form(Database $db, array $user): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    $user_to_edit = isset($_POST['user']) ? mysql_num($_POST['user']) : 0;
    $query = $db->query("SELECT u.username, u.login_name, u.level, u.money, u.moneyChecking, u.moneySavings, u.moneyTreasury, u.respect, u.hospital, u.jail, u.staffnotes, us.strength, us.agility, us.guard, us.labour, us.IQ FROM users u LEFT JOIN userstats us on u.userid = us.userid WHERE u.userid = {$user_to_edit}");
    $row = mysqli_fetch_assoc($query);

    print "
        <h3>Editing Player</h3>
        <form action='staffUsers.php?action=editusersub' method='post'>
            <input type='hidden' name='userid' value='{$user_to_edit}'>
            <table cellspacing='0' cellpadding='3' class='table'>
                <tr>
                    <td>Name:</td>
                    <td><input size='50' type='text' name='username' value='{$row['username']}'></td>
                </tr>
                <tr>
                    <td>Login:</td>
                    <td><input size='50' type='text' name='login_name' value='{$row['login_name']}'></td>
                </tr>
                <tr>
                    <td>Level:</td>
                    <td><input size='50' type='text' name='level' value='{$row['level']}'></td>
                </tr>
                <tr>
                    <td>Money:</td>
                    <td><input size='50' type='text' name='money' value='{$row['money']}'></td>
                </tr>
                <tr>
                    <td>Checking:</td>
                    <td><input size='50' type='text' name='moneyChecking' value='{$row['moneyChecking']}'></td>
                </tr>
                <tr>
                    <td>Savings:</td>
                    <td><input size='50' type='text' name='moneySavings' value='{$row['moneySavings']}'></td>
                </tr>
                <tr>
                    <td>T-Bills:</td>
                    <td><input size='50' type='text' name='moneyTreasury' value='{$row['moneyTreasury']}'></td>
                </tr>
                <tr>
                    <td>Respect:</td>
                    <td><input size='50' type='text' name='respect' value='{$row['respect']}'></td>
                </tr>
                <tr>
                    <td>Hospital time:</td>
                    <td><input size='50' type='text' name='hospital' value='{$row['hospital']}'></td>
                </tr>
                <tr>
                    <td>Jail time:</td>
                    <td><input size='50' type='text' name='jail' value='{$row['jail']}'></td>
                </tr>
                <tr>
                    <td>Strength:</td>
                    <td><input size='50' type='text' name='strength' value='{$row['strength']}'></td>
                </tr>
                <tr>
                    <td>Agility:</td>
                    <td><input size='50' type='text' name='agility' value='{$row['agility']}'></td>
                </tr>
                <tr>
                    <td>Guard:</td>
                    <td><input size='50' type='text' name='guard' value='{$row['guard']}'></td>
                </tr>
                <tr>
                    <td>Labour:</td>
                    <td><input size='50' type='text' name='labour' value='{$row['labour']}'></td>
                </tr>
                <tr>
                    <td>IQ:</td>
                    <td><input size='50' type='text' name='IQ' value='{$row['IQ']}'></td>
                </tr>
                <tr><td colspan='2'> Staff Notes<br> <textarea rows=8 cols=55 name='staffnotes'>{$row['staffnotes']}</textarea></td></tr>
            </table>
            <input type='submit' value='Edit Player'>
        </form>
    ";
}

function edit_user_sub(Database $db, Header $headers, array $user): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    $go = 0;
    if (!isset($_POST['level']) || !isset($_POST['money']) || !isset($_POST['moneyChecking']) ||
        !isset($_POST['moneySavings']) || !isset($_POST['moneyTreasury']) || !isset($_POST['respect']) ||
        !isset($_POST['strength']) || !isset($_POST['agility']) || !isset($_POST['guard']) || !isset($_POST['labour']) ||
        !isset($_POST['IQ']) || !isset($_POST['username']) || !isset($_POST['login_name'])) {
        $go = 1;
    }

    if ($go) {
        print "
            <h3>Edit Player</h3>
            <p>You did not fully fill out the form.</p>
            <p><a href='staffUsers.php?action=edituserform'>Back</a></p>
        ";

        $_POST['user'] = mysql_num($_POST['userid']);
    } else {
        $_POST['level'] = (int)$_POST['level'];
        $_POST['strength'] = abs((int)$_POST['strength']);
        $_POST['agility'] = abs((int)$_POST['agility']);
        $_POST['guard'] = abs((int)$_POST['guard']);
        $_POST['labour'] = abs((int)$_POST['labour']);
        $_POST['IQ'] = abs((int)$_POST['IQ']);
        $_POST['money'] = (int)$_POST['money'];
        $_POST['moneyChecking'] = (int)$_POST['moneyChecking'];
        $_POST['moneySavings'] = (int)$_POST['moneySavings'];
        $_POST['moneyTreasury'] = (int)$_POST['moneyTreasury'];
        $_POST['respect'] = (int)$_POST['respect'];

        //check for username usage
        $u = $db->query("SELECT userid FROM users WHERE username='{$_POST['username']}' and userid != {$_POST['userid']}");
        if (mysqli_num_rows($u) != 0) {
            print "That username is in use, choose another.";
            print "<br><a href='admin.php?action=edituser'>Back</a>";

            $headers->endpage();
            exit;
        }

        $energy = 10 + $_POST['level'] * 2;
        $nerve = 3 + $_POST['level'] * 2;
        $hp = 25 + $_POST['level'] * 6;

        $db->query("UPDATE users SET username = '{$_POST['username']}', level = {$_POST['level']}, money = {$_POST['money']}, respect = {$_POST['respect']}, energy = {$energy}, brave = {$nerve}, maxbrave = {$nerve}, maxenergy = {$energy}, hp = {$hp}, maxhp = {$hp}, hospital = {$_POST['hospital']}, jail = {$_POST['jail']}, staffnotes = '{$_POST['staffnotes']}', login_name = '{$_POST['login_name']}' WHERE userid = {$_POST['userid']}");
        $db->query("UPDATE userstats SET strength = {$_POST['strength']}, agility = {$_POST['agility']}, guard = {$_POST['guard']}, labour = {$_POST['labour']}, IQ = {$_POST['IQ']} WHERE userid = {$_POST['userid']}");

        staffLogAdd("Edited user {$_POST['username']} [{$_POST['userid']}]");
        print "Player edited....";
    }
}

function group_giving_form(Database $db, array $user): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Mass Gifts</h3>
        <p>Remember, this goes to <em>each and every player</em> so keep it lean.</p><br>
        <h5>Item Distribution</h5>
        <form action='staffUsers.php?action=grpgivsubm' method='POST'>
            <input type='radio' name='grp' value='active' checked> Active &nbsp;
            <input type='radio' name='grp' value='online'> Online &nbsp;
            <select name=itm type=dropdown>
    ";

    $query = $db->query("SELECT itmid, itmname FROM items ORDER BY itmname");
    while ($row = mysqli_fetch_assoc($query)) {
        print '<option value=\'' . $row['itmid'] . '\'>' . $row['itmname'] . '</option>';
    }

    print "
            </select> &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Item'>
        </form>
        <br><br>
        <h5>Money Distribution</h5>
        <form action='staffUsers.php?action=grpgivsubm' method='POST'>
            <input type='radio' name='grp' value='active' checked> Active &nbsp;
            <input type='radio' name='grp' value='online'> Online &nbsp;
            Cash: <input type='text' size='10' name='csh'> &nbsp; &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Cash'>
        </form>
        <br><br>
        <h5>Respect Distribution</h5>
        <form action='staffUsers.php?action=grpgivsubm' method='POST'>
            <input type='radio' name='grp' value='active' checked> Active &nbsp;
            <input type='radio' name='grp' value='online'> Online &nbsp;
            Respect: <input type='text' size='5' name='rsp'> &nbsp; &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Respect'>
        </form>
    ";
}

function group_giving_submit(Database $db, array $user, int $csh, string $grp, int $itm, int $rsp, string $txt): void
{
    if ($user['rank'] != 'Capo') {
        unauthorized($user['userid'], 2);
    }

    $test = "trackActionTime>=unix_timestamp()-2592000";
    if ($grp == 'active') {
        $test = "trackActionTime>=unix_timestamp()-1209600";
    } else if ($grp == 'online') {
        $test = "trackActionTime>=unix_timestamp()-900";
    } else {
        $grp = 'most';
    }

    $query = $db->query("SELECT userid FROM users WHERE rankCat = 'Player' AND {$test}");
    while ($row = mysqli_fetch_assoc($query)) {
        if ($csh > 0) {
            $db->query("UPDATE users SET moneyChecking = moneyChecking + {$csh} WHERE userid = {$row['userid']}");

            logEvent($row['userid'], "{$txt} You received " . moneyFormatter($csh) . ".");
            staffLogAdd("Gave $grp users " . moneyFormatter($csh) . ".");

            print "
                <p>The {$grp} Mafioso were each given " . moneyFormatter($csh) . " and told <em>{$txt}</em>.</p>
                <p><a href='staff.php'>Staff Home</a></p>
            ";
        } else if ($rsp > 0) {
            $db->query("UPDATE users SET respect = respect + {$rsp} WHERE userid = {$row['userid']}");

            logEvent($row['userid'], "{$txt} You received " . moneyFormatter($rsp, '') . " tokens of respect.");
            staffLogAdd("Gave {$grp} users " . moneyFormatter($rsp, '') . ".");

            print "
                <p>The {$grp} Mafioso were each given " . moneyFormatter($rsp, '') . " tokens of respect and told <em>{$txt}</em>.</p>
                <p><a href='staff.php'>Staff Home</a></p>
            ";
        } else if ($itm > 0) {
            itemAdd($itm, 0, $row['userid'], 0, 1);
            logEvent($row['userid'], "{$txt} You received a " . itemInfo($itm) . ".");
            staffLogAdd("Gave {$grp} users one " . itemInfo($itm) . ".");

            print "
                <p>The {$grp} Mafioso were each given one " . itemInfo($itm) . " and told <em>{$txt}</em>.</p>
                <p><a href='staff.php'>Staff Home</a></p>
            ";
        }
    }
}

function individual_giving_form(Database $db, array $user): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Individual Gifts</h3>
        <p>Remember, this was not earned, so keep it lean.</p><br>
        <h5>Item Distribution</h5>
        <form action='staffUsers.php?action=indgivsubm' method='POST'>
            " . mafiosoMenu('uid') . " &nbsp;
            <select name=itm type=dropdown>
    ";

    $query = $db->query("SELECT itmid, itmname FROM items ORDER BY itmname");
    while ($row = mysqli_fetch_assoc($query)) {
        print '<option value=\'' . $row['itmid'] . '\'>' . $row['itmname'] . '</option>';
    }

    print "
            </select> &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Item'>
        </form>
        <br><br>
        <h5>Money Distribution</h5>
        <form action='staffUsers.php?action=indgivsubm' method='POST'>
            " . mafiosoMenu('uid') . " &nbsp;
            Cash: <input type='text' size='10' name='csh'> &nbsp; &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Cash'>
        </form>
        <br><br>
        <h5>Respect Distribution</h5>
        <form action='staffUsers.php?action=indgivsubm' method='POST'>
            " . mafiosoMenu('uid') . " &nbsp;
            Respect: <input type='text' size='5' name='rsp'> &nbsp; &nbsp;
            Reason: <input type='text' size='20' name='txt'> &nbsp;
            <input type='submit' value='Give Respect'>
        </form>
    ";
}

function individual_giving_submit(Database $db, array $user, int $csh, int $itm, int $rsp, string $txt, int $uid): void
{
    if ($user['rankCat'] != 'Staff' && $user['rank'] == 'Sgarrista') {
        unauthorized($user['userid'], 2);
    }

    if (!$txt) {
        $txt = "Staff thanks you for your help.";
    }

    if ($csh > 0) {
        $db->query("UPDATE users SET moneyChecking = moneyChecking + {$csh} WHERE userid = {$uid}");

        logEvent($uid, "{$txt} You received " . moneyFormatter($csh) . ".");
        staffLogAdd("Gave " . mafiosoLight($uid) . " " . moneyFormatter($csh) . ".");

        print "
            <p>" . mafioso($uid) . " was given " . moneyFormatter($csh) . " and told <em>{$txt}</em>.</p>
            <p><a href='staff.php'>Staff Home</a></p>
        ";
    } else if ($rsp > 0) {
        $db->query("UPDATE users SET respect = respect + {$rsp} WHERE userid = {$uid}");

        logEvent($uid, "{$txt} You received " . moneyFormatter($rsp, '') . " tokens of respect.");
        staffLogAdd("Gave " . mafiosoLight($uid) . " " . moneyFormatter($rsp, '') . " Token of Respect.");

        print "
            <p>" . mafioso($uid) . " was given " . moneyFormatter($rsp, '') . " and told <em>{$txt}</em>.</p>
            <p><a href='staff.php'>Staff Home</a></p>
        ";
    } else if ($itm > 0) {
        itemAdd($itm, 1, 0, $uid, 0);
        logEvent($uid, "{$txt} You received a " . itemInfo($itm) . ".");
        staffLogAdd("Gave " . mafiosoLight($uid) . " one " . itemInfo($itm) . ".");

        print "
            <p>" . mafioso($uid) . " was given " . itemInfo($itm) . " and told <em>{$txt}</em>.</p>
            <p><a href='staff.php'>Staff Home</a></p>
        ";
    }
}

function ip_search_form(Database $db, array $user): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Search for duplicate IPs</h3><br>
        <form action='staffUsers.php?action=ipsrchsubm' method='GET'>
            IP Address: <input type='text' name='ips' value=''> &nbsp;
            <input type='submit' value='Search'>
        </form>
        <br><br>
        <table class='table' cellpadding='2' cellspacing='0'width=55%>
            <tr>
                <th>Mafioso</th>
                <th>Approved Neighbors</th>
            </tr>
    ";

    $query = $db->query("SELECT COUNT(trackActionIP) as countIP, trackActionIP, userid FROM users WHERE rankCat != 'Staff' AND trackActionIP != '127.1.1.1' GROUP BY trackActionIP, userid HAVING countIP > 1");
    while ($row = mysqli_fetch_assoc($query)) {
        print "<tr><td colspan=2 style='padding-top:1.5em;'>&middot; {$row['trackActionIP']} &middot;</td></tr>";

        $q2 = $db->query("SELECT userid, multiApproved FROM users WHERE trackActionIP = '{$row['trackActionIP']}'");
        while ($r2 = mysqli_fetch_assoc($q2)) {
            print "
                <tr>
                    <td>&nbsp; &nbsp;" . mafioso($r2['userid']) . "</td>
                    <td>{$r2['multiApproved']}</td>
                </tr>
            ";
        }
    }

    print "</table><br>";
}

function ip_search_submit(Database $db, array $user, string $ips): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Search for IP Addresses in use</h3><br>
        <table class='table' cellpadding='2' cellspacing='0'width=55%>
            <tr>
                <th>Last Action</th>
                <th>Approved Neighbors</th>
            </tr>
    ";

    $q1 = $db->query("SELECT userid, multiApproved FROM users WHERE trackActionIP = '{$ips}'");
    while ($r1 = mysqli_fetch_assoc($q1)) {
        print "
            <tr>
                <td>" . mafioso($r1['userid']) . "</td>
                <td>{$r1['multiApproved']}</td>
            </tr>
        ";
    }

    print "
        <tr><td><br></td></tr>
        <tr>
            <th>Last Login</th>
            <th>Approved Neighbors</th>
        </tr>
    ";

    $q2 = $db->query("SELECT userid, multiApproved FROM users WHERE trackActionIP = '{$ips}'");
    while ($r2 = mysqli_fetch_assoc($q2)) {
        print "
            <tr>
                <td>" . mafioso($r2['userid']) . "</td>
                <td>{$r2['multiApproved']}</td>
            </tr>
        ";
    }

    print "
        <tr><td><br></td></tr>
        <tr>
            <th>Signed up</th>
            <th>Approved Neighbors</th>
        </tr>
    ";

    $q3 = $db->query("SELECT userid, multiApproved FROM users WHERE trackSignupIP = '{$ips}'");
    while ($r3 = mysqli_fetch_assoc($q3)) {
        print "
            <tr>
                <td>" . mafioso($r3['userid']) . "</td>
                <td>{$r3['multiApproved']}</td>
            </tr>
        ";
    }

    print "</table><br>";
}

function mafioso_form(Database $db, array $user, int $uid): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    $query = $db->query("SELECT staffnotes FROM users WHERE userid = {$uid};");
    $row = mysqli_fetch_assoc($query);

    print "
        <h3>Mafioso Staff Data</h3><br>
        <form action='staffUsers.php?action=mafiososub' method='POST'>
            <input type='hidden' name='userid' value='{$uid}'>
            <textarea rows=8 cols=55 name='staffnotes'>{$row['staffnotes']}</textarea>
            <input type='submit' value='Edit'>
        </form>
    ";
}

function mafioso_sub(Database $db, array $user, string $txt, string $tx2, int $uid): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Mafioso Staff Data</h3><br>
        <p>Information Updated</p>
        <p><a href='viewuser.php?u={$uid}'>Return to Mafioso</a></p>
    ";

    $db->query("UPDATE users SET staffnotes = '{$txt}', multiApproved = '{$tx2}' WHERE userid = {$uid}");
}

function watchful_eye(Database $db, array $user): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "
        <h3>Super Double Secret Probation</h3><br>
        <table class='table' cellpadding='2' cellspacing='0'width=95%>
            <tr>
                <th>Mafioso</th>
                <th>Notes</th>
            </tr>
    ";

    $qe = $db->query("SELECT staffnotes, userid, watchfulEye FROM users WHERE watchfulEye = 1 ORDER BY staffnotes DESC");
    while ($re = mysqli_fetch_assoc($qe)) {
        print "
            <tr>
                <td valign='top'>" . mafioso($re['userid']) . "</td>
                <td>" . mysql_tex_out($re['staffnotes']) . "<br><br></td>
            </tr>
        ";
    }

    print "</table><br>";
}

function watchful_eye_begin(Database $db, array $user, int $mid): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    $db->query("UPDATE users SET watchfulEye = 1 WHERE userid = {$mid}");

    print "
        <h3>Super Double Secret Probation</h3><br>
        <p>We have to keep a close watch on <a href='staffUsers.php?action=watchfuleye'>these people</a>.</p>
        <p>" . mafioso($mid) . " has been added to the list.</p>
    ";
}

function watchful_eye_end(Database $db, array $user, int $mid): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    $db->query("UPDATE users SET watchfulEye = 0 WHERE userid = {$mid}");

    print "
        <h3>Super Double Secret Probation</h3><br>
        <p>We have to keep a close watch on <a href='staffUsers.php?action=watchfuleye'>these people</a>.</p>
        <p>" . mafioso($mid) . " has been removed from the list.</p>
    ";
}

$application->header->endPage();
