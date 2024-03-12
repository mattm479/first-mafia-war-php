<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$newsignature = isset($_POST['newsignature']) ? mysql_tex($_POST['newsignature']) : '';
$forumSig = isset($_POST['forumSig']) ? mysql_tex($_POST['forumSig']) : '';
$forumMug = isset($_POST['forumMug']) ? mysql_tex($_POST['forumMug']) : '';
$agi = isset($_POST['agi']) ? mysql_num($_POST['agi']) : 0;
$gua = isset($_POST['gua']) ? mysql_num($_POST['gua']) : 0;
$str = isset($_POST['str']) ? mysql_num($_POST['str']) : 0;
$lab = isset($_POST['lab']) ? mysql_num($_POST['lab']) : 0;
$mth = isset($_POST['mth']) ? mysql_tex($_POST['mth']) : '';
$day = isset($_POST['day']) ? mysql_num($_POST['day']) : 0;
$cha = isset($_POST['cha']) ? mysql_num($_POST['cha']) : 0;

switch ($action) {
    case 'birthday' :
        birthday($headers, $user);
        break;
    case 'birthdaydo' :
        birthday_do($db, $userId, $mth, $day, $cha);
        break;
    case 'gym':
        gym($user);
        break;
    case 'gymdo':
        gymdo($db, $headers, $user, $userId, $agi, $gua, $str, $lab);
        break;
    case 'donor':
        conf_donor($user);
        break;
    case 'donor2':
        do_donor($db, $user, $userId);
        break;
    case 'sexchange2':
        do_sex_change($db, $user, $userId);
        break;
    case 'sexchange':
        conf_sex_change($user);
        break;
    case 'passchange2':
        do_pass_change($db, $user, $userId);
        break;
    case 'passchange':
        pass_change();
        break;
    case 'signaturechange':
        signature_change($user);
        break;
    case 'dosignaturechange':
        do_signature_change($db, $userId, $newsignature);
        break;
    case 'picchange2':
        do_pic_change($db, $userId);
        break;
    case 'picchange':
        pic_change($user);
        break;
    case 'forumchange2':
        do_forum_change($db, $userId, $forumSig, $forumMug);
        break;
    case 'forumchange':
        forum_change($user);
        break;
    default:
        header('Refresh:0; url=home.php');
}

function birthday(Header $headers, array $user): void
{
    $birth = unserialize($user['birthday']);

    print '
        <h3>Birthday</h3>
        <p>Your birthday is automatically set to the day you joined.  You may change it - <strong>once</strong> if you wish.  This is the birth day and month of your Mafioso.  On that day you will receive benefits and special attention.  If you wish it to be the same day as your actual birthday, no problem, but that is up to you.</p>
    ';

    if ($birth['cha'] == 1) {
        print '
            <p>Your birthday is ' . $birth['mth'] . ', ' . $birth['day'] . '.  You have already changed it. If you wish to change it again, you may petition staff, but it is unlikely they will allow it.</p>
            <p><a href=\'home.php\'>Return home</a></p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <p><em>Remember, you cannot change this once you have set it, so please be careful.</em></p>
        <form action=\'preferences.php?action=birthdaydo\' method=POST>
            Birth Month:
            <select name=\'mth\'>
                <option value = ' . $birth['mth'] . '>' . $birth['mth'] . '</option>
                <option value = \'January\'>January</option>
                <option value = \'February\'>February</option>
                <option value = \'March\'>March</option>
                <option value = \'April\'>April</option>
                <option value = \'May\'>May</option>
                <option value = \'June\'>June</option>
                <option value = \'July\'>July</option>
                <option value = \'August\'>August</option>
                <option value = \'September\'>September</option>
                <option value = \'October\'>October</option>
                <option value = \'November\'>November</option>
                <option value = \'December\'>December</option>
            </select> &nbsp; &nbsp;
            Birth Day:
            <select name=\'day\'>
                <option value = ' . $birth['day'] . '>' . $birth['day'] . '</option>
                <option value = \'1\'>1</option>
                <option value = \'2\'>2</option>
                <option value = \'3\'>3</option>
                <option value = \'4\'>4</option>
                <option value = \'5\'>5</option>
                <option value = \'6\'>6</option>
                <option value = \'7\'>7</option>
                <option value = \'8\'>8</option>
                <option value = \'9\'>9</option>
                <option value = \'10\'>10</option>
                <option value = \'11\'>11</option>
                <option value = \'12\'>12</option>
                <option value = \'13\'>13</option>
                <option value = \'14\'>14</option>
                <option value = \'15\'>15</option>
                <option value = \'16\'>16</option>
                <option value = \'17\'>17</option>
                <option value = \'18\'>18</option>
                <option value = \'19\'>19</option>
                <option value = \'20\'>20</option>
                <option value = \'21\'>21</option>
                <option value = \'22\'>22</option>
                <option value = \'23\'>23</option>
                <option value = \'24\'>24</option>
                <option value = \'25\'>25</option>
                <option value = \'26\'>26</option>
                <option value = \'27\'>27</option>
                <option value = \'28\'>28</option>
                <option value = \'29\'>29</option>
                <option value = \'30\'>30</option>
                <option value = \'31\'>31</option>
            </select>
            <input type=hidden name=\'cha\' value=1> &nbsp; &nbsp;
            <input type=submit value=\'Set your Birthday\'>
        </form>
    ';
}

function birthday_do(Database $db, int $userId, string $mth, int $day, int $cha): void
{
    $ser = addslashes(serialize(array("mth" => $mth, "day" => $day, "cha" => $cha)));

    $db->query("UPDATE users SET birthday = '{$ser}' WHERE userid = {$userId}");

    print '
        <h3>Birthday</h3>
        <p>Your Birthday has been set. Enjoy.</p>
        <p><a href=\'home.php\'>return home</a></p>
    ';
}

function gym(array $user): void
{
    $statC = unserialize($user['gymPreference']);
    $agiC = $statC['agi'] ?: 0;
    $guaC = $statC['gua'] ?: 0;
    $strC = $statC['str'] ?: 0;
    $labC = $statC['lab'] ?: 0;

    print '
        <h3>Gym Preferences</h3>
        <p>Please make this my preset for gym workouts. These are percentages and the total must be less than 100. When used, it will round your energy to the nearest divisible whole number and leave the rest for the next workout.</p>
        <p>For example if you wanted an even workout you would put 25 in each box. If you want to use workout with just agility, and only do half at a time, put 50 next to agility and 0 next to the others.</p>
        <form action=\'preferences.php?action=gymdo\' method=POST>
            <table cellpadding=2 cellspacing=0 class=table>
                <tr>
                    <td>Agility:</td>
                    <td><input type=text name=\'agi\' size=5 value=\'' . $agiC . '\'></td>
                </tr>
                <tr>
                    <td>Guard:</td>
                    <td><input type=text name=\'gua\' size=5 value=\'' . $guaC . '\'></td>
                </tr>
                <tr>
                    <td>Strength:</td>
                    <td><input type=text name=\'str\' size=5 value=\'' . $strC . '\'></td>
                </tr>
                <tr>
                    <td>Labour:</td>
                    <td><input type=text name=\'lab\' size=5 value=\'' . $labC . '\'></td>
                </tr>
                <tr><td colspan=2><input type=submit value=\'Adjust your custom training\'></td></tr>
            </table>
        </form>
    ';
}

function gymdo($db, $headers, $user, $userId, $agi, $gua, $str, $lab): void
{
    if ($agi + $gua + $str + $lab > 100) {
        gym($user);

        $headers->endpage();
        exit;
    }

    $ser = addslashes(serialize(array("agi" => $agi, "gua" => $gua, "str" => $str, "lab" => $lab)));

    $db->query("UPDATE users SET gymPreference = '{$ser}' WHERE userid = {$userId}");

    print '
        <h3>Gym Preferences</h3>
        <p>Your standard workout has been set. Enjoy.</p>
        <p><a href=\'gym.php\'>return to the gym</a></p>
    ';
}

function conf_donor(array $user): void
{
    if ($user['donateMshow'] == "yes") {
        print '
            <h3>Preferences</h3>
            <p>You are currently ranked in the visible donation list. Your dollar amount will never be listed, but your rank in relation to other donors will be visible.</p>
        ';
    } else {
        print '
            <h3>Preferences</h3>
            <p>You are currently NOT ranked in the visible donation list.</p>
        ';
    }

    print '<p><a href=\'preferences.php?action=donor2\'>Please Change</a> &nbsp;&middot;&nbsp; <a href=\'home.php\'>go home</a></p>';
}

function do_donor(Database $db, array $user, int $userId): void
{
    $g = 'yes';
    if ($user['donateMshow'] == "yes") {
        $g = 'no';
    }

    $db->query("UPDATE users SET donateMshow = '{$g}' WHERE userid = {$userId}");

    print '
        <h3>Preferences</h3>
        <p>Your status has been changed.</p>
        <p><a href=\'home.php\'>Home</a></p>
    ';
}

function conf_sex_change(array $user): void
{
    $g = 'Male';
    if ($user['gender'] == "Male") {
        $g = 'Female';
    }

    print '
        <p>Are you sure you want to become a ' . $g . '?</p>
        <p><a href=\'preferences.php?action=sexchange2\'>Yes</a> &nbsp;&middot;&nbsp; <a href=\'home.php\'>No</a></p>
    ';
}

function do_sex_change(Database $db, array $user, int $userId): void
{
    $g = 'Male';
    if ($user['gender'] == "Male") {
        $g = 'Female';
    }

    $db->query("UPDATE users SET gender = '{$g}' WHERE userid = {$userId}");

    print '
        <p>The operation was a success with few complications, you are now ' . $g . '!</p>
        <p><a href=\'home.php\'>Home</a></p>
    ';
}

function pass_change(): void
{
    print '
        <h3>Password Change</h3>
        <p>
            <form action=\'preferences.php?action=passchange2\' method=POST>
                Current Password: <input type=password name=oldpw><br><br>
                New Password: <input type=password name=newpw><br>
                Confirm: <input type=password name=newpw2><br>
                <input type=submit value=\'Change Password\'>
            </form>
        </p>
    ';
}

function do_pass_change(Database $db, array $user, int $userId)
{
    $newpw = mysqli_real_escape_string($db, $_POST['newpw']);
    if (!password_verify($_POST['oldpw'], $user['userpass'])) {
        print '
            <p>The current password you entered is incorrect.</p>
            <p><a href=\'preferences.php?action=passchange\'>Try again</a></p>
        ';
    } else if ($newpw !== $_POST['newpw2']) {
        print '
            <p>The new passwords you entered did not match. The most likely reason is your possible use of unusual characters. Stick to letters and numbers please.</p>
            <p><a href=\'preferences.php?action=passchange\'>Try Again</a></p>
        ';
    } else {
        $db->query("UPDATE users SET userpass = '" . password_hash($newpw, CRYPT_BLOWFISH) . "' WHERE userid = {$userId}");

        print '
            <p>Password changed successfully!</p>
            <p><a href=\'home.php\'>Home</a></p>
        ';
    }
}

function signature_change(array $user): void
{
    print '
        <h3>Signature Change</h3>
        <p>You may change your signature whenever you want. It is visible if someone rolls over your name with their mouse and if they examine your profile page. It cannot be too long, so you have to be creative.</p>
        <p>Current Signature: ' . mysql_tex_out($user['signature']) . '</p>
        <form action=\'preferences.php?action=dosignaturechange\' method=POST>
            New Signature:<br>
            <input type=text size=40 name=newsignature>
            <input type=submit value=\'Change Signature\'>
        </form>
    ';
}

function do_signature_change(Database $db, int $userId, string $newsignature): void
{
    $newsignature = str_replace("\'", '', $newsignature);
    $newsignature = str_replace("\"", '', $newsignature);

    $db->query("UPDATE users SET signature = '{$newsignature}' WHERE userid = {$userId}");

    print '
        <p>Signature successfully changed!</p>
        <p><a href=\'home.php\'>Home</a></p>
    ';
}

function pic_change(array $user): void
{
    print '
        <h3>Mugshot Change</h3>
        <p>
            Please note that your image must be externally hosted, <a href=\'http://imageshack.us\'>ImageShack</a> does a pretty decent job of it. Remember you ONLY want to use the actual image link, not all the other stuff.  It should look something like this:<br>
            &nbsp; &nbsp; &#104;&#x74;&#116;&#112;&#x3a;&#x2f;&#x2f;&#119;&#x77;&#x77;&#x2e;&#102;&#x69;&#114;&#115;&#x74;&#x6d;&#x61;&#x66;&#105;&#97;&#x77;&#97;&#x72;&#46;&#99;&#111;&#109;&#x2f;&#105;&#109;&#x61;&#x67;&#101;&#115;&#47;&#x6d;&#x61;&#x66;&#105;&#111;&#115;&#x6f;&#47;&#x4b;&#101;&#x66;&#x65;&#46;&#x6a;&#112;&#103;
        </p>
        <p>Any images that are not 150x150 will be automatically resized so if you want it to look good, please provide the right size.</p>
        <form action=\'preferences.php?action=picchange2\' method=POST>
            New mugshot: <input type=text name=newpic size=60 value=\'' . $user['display_pic'] . '\'><br>
            <input type=submit value=\'Change Mugshot\'>
        </form>
    ';
}

function do_pic_change(Database $db, int $userId): void
{
    if ($_POST['newpic'] == '') {
        print '
            <p>You did not enter a new image.</p>
            <p><a href=\'preferences.php?action=picchange\'>Try again</a></p>
        ';
    } else {
        $_POST['newpic'] = mysql_tex($_POST['newpic']);

        $db->query("UPDATE users SET display_pic = '{$_POST['newpic']}' WHERE userid = {$userId}");

        print '
            <p>Mugshot changed!</p>
            <p><a href=\'home.php\'>Home</a></p>
        ';
    }
}

function forum_change(array $user): void
{
    print '
        <h3>Forum Information Change</h3>
        <p>
            Please note that your forum Mugshot must be externally hosted, <a href=\'http://imageshack.us\'>ImageShack</a> is pretty good for the task. Mugshots that are not 100x100 will be automatically resized so if you want it to look good, make sure it is that size. Remember also that you ONLY want to use the actual image link, not all the other stuff.  It should look something like this:<br>
            &nbsp; &nbsp; &#104;&#x74;&#116;&#112;&#x3a;&#x2f;&#x2f;&#119;&#x77;&#x77;&#x2e;&#102;&#x69;&#114;&#115;&#x74;&#x6d;&#x61;&#x66;&#105;&#97;&#x77;&#97;&#x72;&#46;&#99;&#111;&#109;&#x2f;&#105;&#109;&#x61;&#x67;&#101;&#115;&#47;&#x6d;&#x61;&#x66;&#105;&#111;&#115;&#x6f;&#47;&#x4b;&#101;&#x66;&#x65;&#46;&#x6a;&#112;&#103;
        </p>
        <form action=\'preferences.php?action=forumchange2\' method=POST>
            New Mugshot: &nbsp;<input type=text name=forumMug size=60 value=\'' . $user['mugForum'] . '\'><br><br>
            New Signature: <textarea rows=1 cols=30 name=forumSig>' . $user['mugForumSig'] . '</textarea><br><br>
            <input type=submit value=\'Change Forum Information\'>
        </form>
    ';
}

function do_forum_change(Database $db, int $userId, string $forumSig, string $forumMug): void
{
    $db->query("UPDATE users SET mugForum = '{$forumMug}', mugForumSig = '{$forumSig}' WHERE userid = {$userId}");

    print '
        <h3>Forum Information Change</h3>
        <p>Forum information changed!</p>
        <p><a href=\'home.php\'>Home</a></p>
    ';
}

$headers->endpage();
