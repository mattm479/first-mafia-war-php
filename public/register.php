<?php

use Fmw\Database;

$config = require "config/application.php";

$db = new Database($config['database']);
$settings = array();
$result = $db->query("SELECT conf_name, conf_value FROM settings");
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['conf_name']] = $row['conf_value'];
}

$ipAddress = $_SERVER['REMOTE_ADDR'];

function email_ok($email): bool|int
{
    return preg_match('/^[-A-Za-z0-9_.]+@[A-Za-z0-9_-]+([.][A-Za-z0-9_-]+)*[.][A-Za-z]{2,8}$/', $email);
}

$referral = isset($_GET['REF']) ?? 0;
$advertis = isset($_GET['ADS']) ?? 0;
$gender = isset($_POST['gender']) ? mysqli_escape_string($db, $_POST['gender']) : '';
$promo = isset($_POST['promo']) ? mysqli_escape_string($db, $_POST['promo']) : '';
$ref = isset($_POST['referral']) ?? 0;
$ads = isset($_POST['advertis']) ?? 0;
$username = isset($_POST['username']) ? mysqli_escape_string($db, $_POST['username']) : '';
$email1 = isset($_POST['email1']) ? mysqli_escape_string($db, $_POST['email1']) : '';
$email2 = isset($_POST['email2']) ? mysqli_escape_string($db, $_POST['email2']) : '';
$pass1 = isset($_POST['pass1']) ? mysqli_escape_string($db, $_POST['pass1']) : '';
$pass2 = isset($_POST['pass2']) ? mysqli_escape_string($db, $_POST['pass2']) : '';

print "
    <!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html lang='en'>
        <head>
            <title>First Mafia War</title>
            <meta http-equiv='content-type' content='text/html; charset=utf-8'>
            <meta http-equiv='content-style-type' content='text/css'>
            <meta name='copyright' content='Copyright 2008-2009, KEFern. All rights reserved.'>
            <link rel='shortcut icon' href='assets/images/favicon.ico' type='image/x-icon'>
            
            <style type='text/css'>
                @import url(assets/css/styles.css);
            </style>
            
            <!--[if IE]>
            <style>
                html ul {margin: 0em 1em 0em 1em;}
                .content {margin-top:0;}
            </style>
            <![endif]-->
        </head>
        
        <body onload='getme();'>
            <div class='container'><div id='login'>
                <br><br>
                <img src='assets/images/fmw_front_logo.gif' style='margin:10px 0 30px 250px;'>
                <br><br>
                <div class='center'>
";

if (!$username == 0) {
    $q = $db->query("SELECT * FROM users WHERE username='{$username}' OR login_name='{$username}'");
    $q2 = $db->query("SELECT * FROM users WHERE email='{$email1}'");
    $q3 = $db->query("SELECT * FROM users WHERE trackActionIP='$ipAddress' AND userid={$ref}");

    if (!email_ok($email1)) {
        print "
            <p>Your email is invalid. Please go back and try again.</p><br>
            <p><a href='register.php'>Back</a></p><br><br>
        ";
        exit;
    }

    if ($email1 != $email2) {
        print "
            <p>Your emails do not match. Please go back and try again.</p><br>
            <p><a href='register.php'>Back</a></p><br><br>
        ";
        exit;
    }

    if (strlen($username) < 4) {
        print "
            <p>You username is too short. Please go back and try again.</p><br>
            <p><a href='register.php'>Back</a></p><br><br>
        ";
        exit;
    }

    if ($pass1 != $pass2) {
        print "
            <p>Your passwords do not match or you are using unusual characters. Please go back and try again.</p><br>
            <p><a href='register.php'>Back</a></p><br><br>
        ";
        exit;
    }

    if (mysqli_num_rows($q)) {
        print "
            <p>Username already in use. Please go back and try again.</p><br>
            <p><a href='register.php'>Back</a></p><br><br>
        ";
        exit;
    }

    $sm = 1000;
    $bm = 10500;

    if ($promo == "EarlyAdopter") {
        $bm += 500;
    }
    if ($promo == "Kef") {
        $bm += 10000;
    }

    $born = "Birthed: " . date('F j, Y', time());
    if ($ads == 101) {
        $born = "Googled: " . date('F j, Y', time());
    }

    $bmonth = date('F', time());
    $bday = date('j', time());
    $ser = addslashes(serialize(array("mth" => $bmonth, "day" => $bday, "cha" => 0)));
    $ipinfo = '';
    $password = password_hash($pass1, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (username, login_name, userpass, level, money, moneyChecking, respect, donatordays, `rank`, rankCat, energy, maxenergy, will, maxwill, brave, maxbrave, hp, maxhp, location, gender, birthday, email, trackSignupIP, trackSignupInfo, trackSignupTime, watchfulEye, staffnotes, newMail, user_notepad) VALUES( '{$username}', '{$username}', '{$password}', 1, $sm, $bm, 5, 0, 'Inattivo', 'NPC', 12, 12, 150, 150, 7, 7, 25, 25, 0, '{$gender}', '{$ser}', '{$email1}', '$ipAddress', '{$ipinfo}', unix_timestamp(), '1', '{$born}', 1, 'Notes')";
    $db->query($query);
    $i = mysqli_insert_id($db);

    $db->query("INSERT INTO userstats VALUES({$i}, 10, 0, 10, 0, 10, 0, 10, 10)");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (6, 0, {$i}, 0, 1, 'yes')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (11, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (27, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (51, 0, {$i}, 0, 1, 'yes')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (52, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (56, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (72, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (94, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (601, 0, {$i}, 0, 1, 'no')");
    $db->query("INSERT INTO inventory (inv_itemid, inv_itmexpire, inv_userid, inv_famid, inv_qty, inv_equip) VALUES (636, 0, {$i}, 0, 1, 'no')");

    $subject = "First Mafia War Registration";
    $body = "{$username},\nThis message has been automatically created to confirm your registration at First Mafia War. Due to that small number of annoying people who try to ruin it for all, we manually check all applicants. So you may login shortly and you will receive an email to notify you. Enjoy the game and welcome to First Mafia War!\n\n-Kef";
    $headers = "From: kefern@firstmafiawar.com\r\n";

    /*if ($ref > 0) {
        $db->query("UPDATE users SET donatordays = donatordays + 3 WHERE userid = {$i}");
        itemAdd(75, 0, $i, 0, 1);
        $db->query("UPDATE users SET respect = respect + 5 WHERE userid = {$ref}");
        logEvent($ref, "Thank you for referring " . mafiosoLight($i) . "!");
        $qr = $db->query("SELECT trackActionIP FROM users WHERE userid = {$ref}");
        $rr = mysqli_fetch_assoc($qr);
        $db->query("INSERT INTO referals (refREFER, refREFED, refTIME, refREFERIP, refREFEDIP) VALUES ({$ref}, $i, unix_timestamp(),'{$rr['trackActionIP']}','{$_SERVER['REMOTE_ADDR']}')");
    }*/

    $subj = 'Welcome to the First Mafia War';
    $msg = 'Welcome! If you have questions, check the Wiki on the bottom left of your screen and the forums. They are pretty thorough, but the game is simpler than it looks. You can also ask around - there are a lot of helpful folks.<br><br>It is hard to start out so you have begun in the Manor House a place of safety only for new Mafioso. When you are ready to join the others just click the link at the top of the page and get a free flight to Palermo. You will also receive gifts every time you gain a level for a short time.<br><br>Enjoy, and remember to have a good time!';
    $db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, 1, {$i}, unix_timestamp(),'{$subj}','{$msg}','Inbox')");

    print "
            <br><br>
            <h3>Registration Successful</h3>
            <p>You have registered and are approved.<br>You may login at any time.</p>
            <p>Thank you for joining us.</p>
            <p><a href='/'>Proceed</a></p>
            <img src='assets/images/photos/streetAftermath.jpg' width='400' height='254' alt='Street Fight Aftermath'>
        </div>
    ";
} else {
    print "
        <div align='center'>
            <h1>Registration</h1>
            <form action=register.php method='POST'>
                <table width='550px' class='table' cellspacing='0' cellpadding='3'>
                    <tr>
                        <td width='150'>Username</td>
                        <td width='150'><input type=text name='username'></td>
                        <td rowspan='7'><img src='assets/images/photos/funeral.jpg' width='360' height='227' alt='funeral'></td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td><input type=password id='pass1' name=pass1></td>
                    </tr>
                    <tr>
                        <td>Confirm Password</td>
                        <td><input type=password id='pass2' name=pass2></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type=text name='email1'></td>
                    </tr>
                    <tr>
                        <td>Confirm Email</td>
                        <td><input type=text name='email2'></td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td colspan='2'>
                            <select name='gender' type='dropdown'>
                                <option value='Female'>Female</option>
                                <option value='Male'>Male</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Promotion</td>
                        <td colspan=2>
                            <input type=text name=promo value=''>
                            <input type=hidden name='referral' value='{$referral}'>
                            <input type=hidden name='advertis' value='{$advertis}'>
                        </td>
                    </tr>
                    <tr><td colspan=2 align=center><br><input type=submit value=Register></td></tr>
                </table>
            </form>
            <br><br><br><br>
            <p><a href='index.php'>Return to Login</a></p>
        </div>
    ";
}

print "
                    <br><br>
                    <p style='text-align:center;'><a href='about.html'><strong>More Information</strong></a></p>
                    <br><br><br><br>
                </div>
                <div class='footer'>
                    <a href='legal.html'>Copyright &copy; Boomer&trade; 2023. All rights reserved.</a> &nbsp; 
                    <a href='mailto:%68%65%6c%70%40%66%69%72%73%74%6d%61%66%69%61%77%61%72%2e%63%6f%6d'>&#69;&#109;&#97;&#105;&#108;&#32;&#85;&#115;</a>
                </div>
            </div>
        </body>
    </html>
";
