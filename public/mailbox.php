<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$ID = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$ID2 = isset($_GET['ID2']) ? mysql_num($_GET['ID2']) : 0;
$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$dir = isset($_GET['dir']) ? mysql_tex($_GET['dir']) : '';
$red = isset($_GET['red']) ? mysql_num($_GET['red']) : 0;
$redID = isset($_GET['redID']) ? mysql_num($_GET['redID']) : 0;
$directory = isset($_GET['directory']) ? mysql_tex($_GET['directory']) : 'Inbox';
$subject = isset($_POST['subject']) ? mysql_tex($_POST['subject']) : '';
$message = isset($_POST['message']) ? mysql_tex($_POST['message']) : '';
$forward = isset($_POST['forward']) ? mysql_tex($_POST['forward']) : '';
$mailTo = isset($_POST['mailTo']) ? mysql_num($_POST['mailTo']) : 0;

$smail = mysqli_fetch_assoc($db->query("SELECT mail_id FROM mail WHERE mail_directory = 'Staff'"));
if ($red == 1) {
    $db->query("UPDATE mail SET mail_directory = 'General' WHERE mail_id = {$redID}");
}

if ($user['gagOrder']) {
    print '
        <h3 style=\'font-color:red;\'>Gag Order in force</h3>
        <p>You have been banned from communicating with others for ' . $user['gagOrder'] . ' more hours.</p>
        <p>The main reason was ' . $user['gagReason'] . '. I\'m sure there were others as well that went undocumented. Try and be more polite please.</p>
    ';

    $headers->endpage();
    exit;
}

print '
    <h3>
        Mail &nbsp; 
        <span class=light>(' . $directory . ') &nbsp; &nbsp; <a class=lighter href=\'mailbox.php?action=compose&directory=Compose\'>Compose</a></span>
    </h3>
    <a href=\'mailbox.php?action=read&directory=Inbox\'>Inbox</a> &nbsp;&middot;&nbsp;
    <a href=\'mailbox.php?action=read&directory=General\'>General</a> &nbsp;&middot;&nbsp;
    <a href=\'mailbox.php?action=read&directory=Sent\'>Sent</a> &nbsp;&middot;&nbsp;
    <a href=\'mailbox.php?action=read&directory=Archive\'>Archive</a>
';

if ($user['rankCat'] == 'Staff' && $smail != null && $smail['mail_id'] > 0) {
    print ' &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=read&directory=Staff\'><strong>Staff Mail</strong></a><br>';
} else {
    print '<br>';
}

switch ($action) {
    case 'compose':
        mail_compose($db, $userId, $ID, $ID2);
        break;
    case 'directory':
        mail_directory($db, $userId, $ID, $dir);
        break;
    case 'send':
        mail_send($db, $headers, $userId, $mailTo, $subject, $message, $forward);
        break;
    case 'read':
    default:
        mail_read($db, $user, $userId, $directory);
        break;
}

function mail_compose(Database $db, int $userId, int $ID, int $ID2): void
{
    print '
        <form action=\'mailbox.php?action=send\' method=POST>
            <table width=95% cellpadding=3 cellspacing=0 class=table>
                <tr>
    ';

    if ($ID > 0) {
        print '<br><td>Username:</td><td>' . mafioso($ID) . '<input type=hidden name=mailTo value=' . $ID . '></td>';
    } else {
        print '<br><td>Mail to:</td><td>' . mafiosoMenu('mailTo') . '</td>';
    }

    print '
        </tr>
        <tr>
            <td>Subject:</td>
            <td><input type=text size=30 name=subject></td>
        </tr>
        <tr>
            <td>Message:</td>
            <td><textarea rows=5 cols=60 name=message></textarea>
        </td>
    ';

    if ($ID2 > 0) {
        $rmai = mysqli_fetch_assoc($db->query("SELECT mail_from, mail_to, mail_time, mail_subject, mail_text FROM mail WHERE mail_id = {$ID2}"));
        $forward = 'From: ' . mafiosoName($rmai['mail_from']) . ' To: ' . mafiosoName($rmai['mail_to']) . ' On: ' . date('F j Y', $rmai['mail_time']) . ' at ' . date('g:i a', $rmai['mail_time']);
        $forward .= " Subject: " . mysql_tex_out($rmai['mail_subject']) . "<br>" . mysql_tex_out($rmai['mail_text']);
        print "
            </tr>
            <tr>
                <td>Forward<br>Message</td>
                <td><em>{$forward}</em><input type=hidden name=forward value=\"{$forward}\"></td>
        ";
    }

    print '
                </tr>
                <tr><td><br></td></tr>
                <tr>
                    <td><input type=submit value=\'Send Mail\'></td>
                    <td><p><br>You may use &lt;strong&gt;<strong>bold text</strong>&lt;/strong&gt; and &lt;em&gt;<em>italic text</em>&lt;/em&gt;.</p></td>
                </tr>
            </table>
        </form><br>
    ';

    if ($ID > 0 && $ID != 22) {
        print '
            <br><hr><br>Your last few emails to and from ' . mafioso($ID) . '.<br>
            <table width=95% class=table border=0 cellpadding=3 cellspacing=0>
        ';

        $query = $db->query("SELECT m.mail_subject, m.mail_text, m.mail_time, u1.username as sender from mail m left join users u1 on m.mail_from = u1.userid WHERE (m.mail_from = {$userId} AND m.mail_to = {$ID}) OR (m.mail_to = {$userId} AND m.mail_from = {$ID}) ORDER BY m.mail_time DESC LIMIT 8");
        while ($row = mysqli_fetch_assoc($query)) {
            print '
                <tr><td class=mostborders><strong>' . mysql_tex_out($row['mail_subject']) . '</strong></td></tr>
                <tr><td class=mostborders style=\'padding-left:.5em;\'>' . mysql_tex_out($row['mail_text']) . '</td></tr>
                <tr><td class=fewborders><span class=light>on ' . date('F j Y', $row['mail_time']) . ' at ' . date('g:i a', $row['mail_time']) . '</span> &nbsp;</td></tr>
                <tr><td></td></tr>
            ';
        }

        print '</table>';
    }
}

function mail_directory(Database $db, int $userId, int $ID, string $dir): void
{
    $db->query("UPDATE mail SET mail_directory = '{$dir}' WHERE mail_id = {$ID} AND mail_to = {$userId}");
    print "
        <br><br><p>Your mail has been put in the {$dir} directory.</p>
        <p><a href='mailbox.php'>Mail</a></p>
    ";
}

function mail_read(Database $db, array $user, int $userId, string $directory): void
{
    print '<br><table width=95% class=table cellspacing=0 cellpadding=3>';

    if ($directory == 'Staff' && $user['rankCat'] == 'Staff') {
        $query = $db->query("SELECT mail_id, mail_from, mail_to, mail_subject, mail_text, mail_time FROM mail WHERE mail_directory = 'Staff' ORDER BY mail_time DESC LIMIT 30");
    } elseif ($directory == 'Sent') {
        $query = $db->query("SELECT mail_id, mail_from, mail_to, mail_subject, mail_text, mail_time FROM mail WHERE mail_from = {$userId} ORDER BY mail_time DESC LIMIT 30");
    } else {
        $query = $db->query("SELECT mail_id, mail_from, mail_to, mail_subject, mail_text, mail_time FROM mail WHERE mail_to = {$userId} AND mail_directory = '{$directory}' ORDER BY mail_time DESC LIMIT 30");
    }

    while ($row = mysqli_fetch_assoc($query)) {
        if ($directory == 'Staff') {
            $actions = '<a href=\'mailbox.php?action=compose&ID=' . $row['mail_from'] . '&red=1&redID=' . $row['mail_id'] . '&ID2=' . $row['mail_id'] . '\'><strong>Reply and claim</strong></a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=compose&ID2=' . $row['mail_id'] . '&red=1&redID=' . $row['mail_id'] . '\'><strong>Forward and assign</strong></a>';
        } elseif ($directory == 'Sent') {
            $actions = '<a href=\'mailbox.php?action=compose&ID2=' . $row['mail_id'] . '\'><strong>Forward</strong></a>';
        } else {
            $actions = '<a href=\'mailbox.php?action=compose&ID=' . $row['mail_from'] . '\'><strong>Mail Sender</strong></a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=compose&ID=' . $row['mail_from'] . '&red=1&redID=' . $row['mail_id'] . '&ID2=' . $row['mail_id'] . '\'><strong>Reply</strong></a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=compose&ID2=' . $row['mail_id'] . '&red=1&redID=' . $row['mail_id'] . '\'><strong>Forward</strong></a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=directory&dir=Archive&ID=' . $row['mail_id'] . '\'>Archive</a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=directory&dir=General&ID=' . $row['mail_id'] . '\'>General</a> &nbsp;&middot;&nbsp; <a href=\'mailbox.php?action=directory&dir=Delete&ID=' . $row['mail_id'] . '\'>Delete</a>';
        }

        print '<tr><td class=mostborders><div class=floatright>' . $actions . ' &nbsp;</div><strong>';

        if ($directory == 'Sent') {
            print 'To: ' . mafiosoLight($row['mail_to']) . ' &nbsp; Subject: ';
        }

        print mysql_tex_out($row['mail_subject']) . '</strong></td></tr><tr><td class=mostborders style=\'padding-left:.5em;\'>' . mysql_tex_out($row['mail_text']) . '</td></tr><tr><td class=fewborders>' . mafiosoLight($row['mail_from']) . ' <span class=light>on ' . date('F j Y', $row['mail_time']) . ' at ' . date('g:i a', $row['mail_time']) . '</span> &nbsp;</td></tr><tr><td></td></tr>';
    }

    print '</table>';

    if ($directory == 'Inbox' & $user['newMail'] > 0) {
        $db->query("UPDATE mail SET mail_read = 1 WHERE mail_to = {$userId}");
        $db->query("UPDATE users SET newMail = 0 WHERE userid = {$userId}");
    }
}

function mail_send(Database $db, Header $headers, int $userId, int $mailTo, string $subject, string $message, string $forward): void
{
    $query = $db->query("SELECT userid FROM users WHERE userid = {$mailTo}");
    if (mysqli_num_rows($query) == 0 && $mailTo != 22) {
        print '
            <br><br><p>You cannot send mail to nonexistent users or Giovanni. They don\'t exist either.</p>
            <p><a href=\'mailbox.php\'>Mail</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($forward) {
        $message = $message . '<br><br><span class=staffview><blockquote>' . mysql_tex_out($forward) . '</blockquote></span>';
    }

    if ($mailTo == 22) {
        $db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, {$userId}, {$mailTo}, unix_timestamp(), '{$subject}', '{$message}', 'Staff')");
        $db->query("UPDATE users SET newMail = newMail + 1 WHERE rankCat = 'Staff'");
    } else {
        $db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, {$userId}, {$mailTo}, unix_timestamp(), '{$subject}', '{$message}', 'Inbox')");
        $db->query("UPDATE users SET newMail = newMail + 1 WHERE userid = {$mailTo}");
    }

    print '
        <br><br>
        <p>Message sent.</p>
        <p><a href=\'mailbox.php\'>Mail</a></p>
    ';
}

$headers->endpage();
