<?php

use Fmw\Database;
use Fmw\Header;

require_once "sglobals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 1, $njl = 0, $nhsp = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$st = isset($_GET['st']) ? mysql_num($_GET['st']) : 0;

if ($user['rankCat'] != 'Staff') {
    print "<p>You must be a member of the staff to view these logs.</p>";

    $headers->endpage();
    exit;
}

switch ($action) {
    case 'attlog':
        attack_logs($db, $headers, $user, $st);
        break;
    case 'donlog':
        donation_logs($db, $headers, $user, $st);
        break;
    case 'eventlogs':
        view_event_logs($db, $user, $st);
        break;
    case 'itmlogs':
        view_itm_logs($db, $user, $st);
        break;
    case 'maillogs':
        view_mail_logs($db, $headers, $user, $st);
        break;
    case 'referrals':
        view_referrals_logs($db, $headers, $user);
        break;
    case 'stafflogs':
        view_staff_logs($db, $headers, $user, $st);
        break;
    case 'wealthlogs':
        view_wealth_logs($db, $headers, $user, $st);
        break;
    default:
        print "Error: This script requires an action.";
        break;
}

function attack_logs(Database $db, Header $headers, array $user, int $st): void
{
    $sevendaysago = time() - (7 * 24 * 60 * 60);
    if ($user['rankCat'] != 'Staff') {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    print "<h3>Attack Logs</h3>";

    $app = 100;
    $query = $db->query("SELECT laID FROM logsAttacks WHERE laTime > {$sevendaysago}");
    $attacks = mysqli_num_rows($query);
    $pages = ceil($attacks / $app);

    print "Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=attlog&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    print "
        <br><br>
        <table width='95%' cellspacing='0' cellpadding='2' class='table' style='font-size:smaller;'>
            <tr>
                <th>Date</th>
                <th>Attack Result</th>
            </tr>
    ";

    $query = $db->query("SELECT laTime, laLogLong, laDefender, laLogShort FROM logsAttacks WHERE laTime > {$sevendaysago} ORDER BY laTime DESC LIMIT {$st}, {$app}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td class='light'>" . date('F j Y', $row['laTime']) . " at " . date('g:i a', $row['laTime']) . "</td>
                <td title='{$row['laLogLong']}'>" . mafiosoLight($row['laDefender']) . " {$row['laLogShort']}</td>
            </tr>
        ";
    }

    print "</table><br>";

    staffLogAdd("Examined attack logs");
}

function donation_logs(Database $db, Header $headers, array $user, int $st): void
{
    if ($user['userid'] != 1) {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    $app = 100;
    $query = $db->query("SELECT ldID FROM logsDonations");
    $attacks = mysqli_num_rows($query);
    $pages = ceil($attacks / $app);

    print "Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=donlog&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    $thirtydaysago = time() - (30 * 24 * 60 * 60);
    print "
        <h3>Donation Logs</h3>
        <div class='floatrightbox'>
            <h5>Top Donators</h5>
            <em>All Time</em><br>
    ";

    $qdon = $db->query("SELECT ldBuyer, sum(ldValue) AS sumValue FROM logsDonations GROUP BY ldBuyer ORDER BY sumValue DESC LIMIT 20");
    while ($row = mysqli_fetch_assoc($qdon)) {
        print " &nbsp;&middot;&nbsp; " . mafiosoLight($row['ldBuyer']) . " " . moneyFormatter($row['sumValue']) . "<br>";
    }

    print "<br><em>Last 30 days</em><br>";
    $qdon = $db->query("SELECT ldBuyer, sum(ldValue) AS sumValue FROM logsDonations WHERE ldTime > {$thirtydaysago} GROUP BY ldBuyer ORDER BY sumValue DESC LIMIT 20");
    while ($row = mysqli_fetch_assoc($qdon)) {
        print " &nbsp;&middot;&nbsp; " . mafiosoLight($row['ldBuyer']) . " " . moneyFormatter($row['sumValue']) . "<br>";
    }

    print "<br><em>This month</em><br>";
    $qdon = $db->query("SELECT donatedM, userid FROM users WHERE donatedM > 0 ORDER BY donatedM DESC LIMIT 20");
    while ($row = mysqli_fetch_assoc($qdon)) {
        print " &nbsp;&middot;&nbsp; " . mafiosoLight($row['userid']) . " " . moneyFormatter($row['donatedM']) . "<br>";
    }

    print "
        </div>
        <table width='70%' cellspacing='0' cellpadding='3' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Player/Email</th>
                <th>Donator Pack</th>
                <th>Cost</th>
            </tr>
    ";

    $query = $db->query("SELECT ldTime, ldBuyer, ldEmail, ldDP, ldValue FROM logsDonations ORDER BY ldTime DESC LIMIT {$st}, {$app}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td>" . date('m/j/y, g:ia', $row['ldTime']) . "</td>
                <td>" . mafiosoLight($row['ldBuyer']) . "<br>{$row['ldEmail']}</td>
                <td>" . itemInfo($row['ldDP']) . "</td>
                <td>" . moneyFormatter($row['ldValue']) . "</td>
            </tr>
        ";
    }

    print "</table>";

    staffLogAdd("Viewed Donation Logs");
}

function view_event_logs(Database $db, array $user, int $st): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    $app = 250;
    $query = $db->query("SELECT leID FROM logsEvents");
    $attacks = mysqli_num_rows($query);
    $pages = ceil($attacks / $app);

    print "Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=eventlogs&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    print "
        <h3>Event Logs</h3>
        <table width='95%' cellspacing='0' cellpadding='3' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Player</th>
                <th>Event</th>
            </tr>
    ";

    $query = $db->query("SELECT leTime, leUser, leText FROM logsEvents ORDER BY leTime DESC LIMIT {$st}, {$app}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td>" . date('m/j/y, g:ia', $row['leTime']) . "</td>
                <td>" . mafiosoLight($row['leUser']) . "</td>
                <td>{$row['leText']}</td>
            </tr>
        ";
    }

    print "</table>";

    staffLogAdd("Viewed Events");
}

function view_itm_logs(Database $db, array $user, int $st): void
{
    if ($user['rankCat'] != 'Staff') {
        unauthorized($user['userid'], 1);
    }

    print "<h3>Item Transfer Logs</h3>";

    $app = 100;
    $query = $db->query("SELECT liID FROM logsItems");
    $attacks = mysqli_num_rows($query);
    $pages = ceil($attacks / $app);

    print "Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=itmlogs&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    print "
        <h5>Item Usage</h5>
        <table width='95%' cellspacing='0' cellpadding='1' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Item</th>
                <th>Reason</th>
                <th>Warning</th>
            </tr>
    ";

    $qw = $db->query("SELECT liSenderIP, liReceiverIP, liSender, liReceiver, liTime, liQuantity, liReason FROM logsItems ORDER BY liTime DESC LIMIT {$st}, {$app}");
    while ($il = mysqli_fetch_assoc($qw)) {
        $m = "";
        if ($il['liSenderIP'] == $il['liReceiverIP'] and $il['liSender'] != $il['liReceiver']) {
            $m = "<span style='color:red;'><strong>Same IP</strong></span>";
        }

        print "
            <tr>
                <td valign='top'>" . date('m/j/y, g:ia', $il['liTime']) . "</td>
                <td valign='top'>" . mafiosoLight($il['liSender']) . "</td>
                <td valign='top'>" . mafiosoLight($il['liReceiver']) . "</td>
                <td valign='top'>" . iteminfo($il['liItem']) . " x{$il['liQuantity']}</td>
                <td valign='top'>{$il['liReason']}</td>
                <td valign='top'>{$m}</td>
            </tr>
        ";
    }

    print"</table>";

    staffLogAdd("Examined Item Transfer Logs");
}

function view_mail_logs(Database $db, Header $headers, array $user, int $st): void
{
    if ($user['rankCat'] != 'Staff') {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Mail Logs</h3>
        <table width='95%' cellspacing='0' cellpadding='1' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Author/Recipient</th>
                <th>Message</th>
            </tr>
    ";

    $rpp = 100;
    $query = $db->query("SELECT m.mail_time, m.mail_from, m.mail_to, m.mail_subject, m.mail_text, u1.username as sender, u2.username as sent FROM mail m LEFT JOIN users u1 ON m.mail_from = u1.userid LEFT JOIN users u2 ON m.mail_to = u2.userid WHERE m.mail_from != 0 ORDER BY m.mail_time DESC LIMIT {$st}, {$rpp}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td valign='top'>" . date('m/j/y, g:ia', $row['mail_time']) . "</td>
                <td valign='top'>" . mafiosoLight($row['mail_from']) . "<br>" . mafiosoLight($row['mail_to']) . "</td>
                <td valign='top' title='{$row['mail_subject']}'>" . mysql_tex_out($row['mail_text']) . "</td>
            </tr>
            <tr><td colspan='4' style='border-bottom: thin solid;'>&nbsp;</td></tr>
        ";
    }

    print "</table><br><br>";

    $q2 = $db->query("SELECT mail_id FROM mail WHERE mail_from != 0");
    $rs = mysqli_num_rows($q2);
    $pages = ceil($rs / $rpp);

    print "Pages: ";
    for ($i = 1; $i <= $pages; $i++) {
        $st = ($i - 1) * $rpp;
        print "<a href='staffLogs.php?action=maillogs&st={$st}'>{$i}</a>&nbsp;";

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    staffLogAdd("Viewed Mail Logs");
}

function view_referrals_logs(Database $db, Header $headers, array $user): void
{
    if ($user['rankCat'] != 'Staff') {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Referral Tracking</h3>
        <table width='95%' cellspacing='0' cellpadding='3' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Referrer</th>
                <th>Referred</th>
                <th>Level</th>
                <th>Warning</th>
            </tr>
    ";

    $query = $db->query("SELECT rf.refREFERIP, rf.refREFEDIP, rf.refREFED, rf.refREFER, rf.refTIME, u1.userid, u2.userid FROM referals rf LEFT JOIN users u1 ON rf.refREFER = u1.userid LEFT JOIN users u2 ON rf.refREFED = u2.userid ORDER BY refTIME DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        $m = "";
        if ($row['refREFERIP'] == $row['refREFEDIP']) {
            $m = "<span style='color:red;'><strong>Same IP</strong></span>";
        }

        $q2 = $db->query("SELECT userid, level FROM users WHERE userid = {$row['refREFED']}");
        $r2 = mysqli_fetch_assoc($q2);

        print "
            <tr>
                <td>" . date('m/j/y, g:ia', $row['refTIME']) . "</td>
                <td>" . mafiosoLight($row['refREFER']) . "<br>({$row['refREFERIP']})</td>
                <td>" . mafiosoLight($row['refREFED']) . "<br>({$row['refREFEDIP']})</td>
                <td>{$r2['level']}</td>
                <td>{$m}</td>
            </tr>
        ";
    }

    print "</table>";

    staffLogAdd("Viewed Referral Tracking Logs");
}

function view_staff_logs(Database $db, Header $headers, array $user, int $st): void
{
    if ($user['rankCat'] != 'Staff') {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    print "
        <h3>Staff Logs</h3>
        <table width='95%' cellspacing='0' cellpadding='3' class='table' style='font-weight:lighter;'>
            <tr>
                <th>Time</th>
                <th>Staffer</th>
                <th>Action</th>
            </tr>
    ";

    $rpp = 100;
    $query = $db->query("SELECT s.time, s.user, s.ip, s.action, u.userid FROM stafflog AS s LEFT JOIN users AS u ON s.user = u.userid ORDER BY s.time DESC LIMIT {$st}, {$rpp}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td>" . date('m/j/y, g:ia', $row['time']) . "</td>
                <td>" . mafiosoLight($row['user']) . " &nbsp; ({$row['ip']})</td>
                <td>{$row['action']}</td>
            </tr>
        ";
    }

    print "</table><br><br>";

    $q2 = $db->query("SELECT id FROM stafflog");
    $rs = mysqli_num_rows($q2);
    $pages = ceil($rs / $rpp);

    print "Pages: ";
    for ($i = 1; $i <= $pages; $i++) {
        $st = ($i - 1) * $rpp;
        print "<a href='staffLogs.php?action=stafflogs&st={$st}'>{$i}</a>&nbsp;";

        if ($i % 30 == 0) {
            print "<br>";
        }
    }
}

function view_wealth_logs(Database $db, Header $headers, array $user, int $st): void
{
    if ($user['rankCat'] != 'Staff') {
        print "<p>You must be a member of the staff to view these logs.</p>";

        $headers->endpage();
        exit;
    }

    $q = $db->query("SELECT lwSender FROM logsWealth");
    $transfers = mysqli_num_rows($q);
    $app = 100;
    $pages = ceil($transfers / $app);

    print "Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=wealthlogs&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    print "
        <h3>Wealth Transfers</h3>
        <table width='95%' cellspacing='0' cellpadding='3' class='table' style='font-size:smaller;'>
            <tr>
                <th>Time</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Source</th>
                <th>Warning</th>
            </tr>
    ";

    $query = $db->query("SELECT lwTime, lwSenderIP, lwSender, lwReceiver, lwReceiverIP, lwType, lwAmount, lwSource FROM logsWealth ORDER BY lwTime DESC LIMIT {$st}, {$app}");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td>" . date('m/j/y', $row['lwTime']) . "<br>" . date('g:ia', $row['lwTime']) . "</td>
        ";

        if ($row['lwSenderIP'] == 'crime ID') {
            print "<td>{$row['lwSender']}<br>({$row['lwSenderIP']})</td>";
        } else {
            print "<td>" . mafiosoLight($row['lwSender']) . "<br>({$row['lwSenderIP']})</td>";
        }

        print "<td>" . mafiosoLight($row['lwReceiver']) . "<br>({$row['lwReceiverIP']})</td>";

        if ($row['lwType'] == 'respect') {
            print "<td style='text-align:right;'>" . moneyFormatter($row['lwAmount'], "") . "</td>";
        } else {
            print "<td style='text-align:right;'>" . moneyFormatter($row['lwAmount']) . "</td>";
        }

        print "<td text-align='left'>{$row['lwType']}</td>";
        print "<td>{$row['lwSource']}</td>";

        if ($row['lwSenderIP'] == $row['lwReceiverIP']) {
            print "<td><span style='color:red;'><strong>Same IP</strong></span></td>";
        } else {
            print "<td>&nbsp;</td>";
        }

        print "</tr>";
    }

    print "</table> Pages:&nbsp;";
    for ($i = 1; $i <= $pages; $i++) {
        $s = ($i - 1) * $app;
        if ($s == $st) {
            print "<strong>{$i}</strong>&nbsp;";
        } else {
            print "<a href='staffLogs.php?action=wealthlogs&st={$s}'>{$i}</a>&nbsp;";
        }

        if ($i % 30 == 0) {
            print "<br>";
        }
    }

    staffLogAdd("Viewed Wealth Logs");
}

$headers->endpage();
