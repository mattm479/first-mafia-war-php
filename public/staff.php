<?php

use Fmw\Database;

require_once "sglobals.php";
global $application;
pagePermission($lgn = 1, $stff = 1, $njl = 0, $nhsp = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$do     = isset($_GET['do']) ? mysql_tex($_GET['do']) : '';
$id     = isset($_GET['id']) ? mysql_num($_GET['id']) : 0;
$text   = isset($_POST['magText']) ? mysql_tex($_POST['magText']) : '';
$min    = isset($_POST['min']) ? mysql_num($_POST['min']) : 0;
$max    = isset($_POST['max']) ? mysql_num($_POST['max']) : 0;
$start  = isset($_POST['start']) ? mysql_num($_POST['start']) : 0;
$end    = isset($_POST['end']) ? mysql_num($_POST['end']) : 0;
$class  = isset($_POST['class']) ? mysql_num($_POST['class']) : 0;
$grand  = isset($_POST['grand']) ? mysql_num($_POST['grand']) : 0;
$gift   = isset($_POST['gift']) ? mysql_num($_POST['gift']) : 0;
$loser  = isset($_POST['loser']) ? mysql_num($_POST['loser']) : 0;
$pad    = isset($_POST['pad']) ? mysql_tex($_POST['pad']) : '';

switch ($action) {
    case 'announce':
        announcements($application->db, $application->user, $text);
        break;
    case 'basicset':
        basicsettings($application->db, $application->user, $application->settings);
        break;
    case 'mafiainquirer':
        mafiainquirer($application->db, $application->user, $do, $id, $text);
        break;
    case 'massmailer':
        massmailer($application->db, $application->user, $application->user['userid'], $text);
        break;
    case 'poll':
        poll($application->db, $application->user);
        break;
    case 'startpoll':
        startpollsub($application->db, $application->user);
        break;
    case 'serverinfo':
        serverinfo($application->user);
        break;
    case 'streetfight':
        street_fight($application->db, $text, $min, $max, $start, $end, $class, $grand, $gift);
        break;
    default:
        index($application->db, $application->user, $application->settings, $pad);
        break;
}

function basicsettings(Database $db, array $user, array $set): void
{
    if ($user['userid'] != 1) {
        header("Location: home.php");
    }

    if (isset($_POST['submit'])) {
        unset($_POST['submit']);
        foreach ($_POST as $k => $v) {
            $db->query("UPDATE settings SET conf_value = '{$v}' WHERE conf_name = '{$k}'");
        }

        staffLogAdd("Updated game settings");
        print "
            <h3>Basic Settings</h3>
            <p>Settings updated!<br><a href='staff.php'>Staff Home</a></p>
        ";
    } else {
        print "
            <h3>Basic Settings</h3>
            <form action='staff.php?action=basicset' method='post'>
                <input type='hidden' name='submit' value='1'>
                <table border='0' cellspacing='0' cellpadding='3' class='table'>
                    <tr>
                        <td>Game Name:</td>
                        <td><input type='text' name='game_name' value='{$set['game_name']}'></td>
                    </tr>
                    <tr>
                        <td>Game Owner:</td>
                        <td><input type='text' name='game_owner' value='{$set['game_owner']}'></td>
                    </tr>
                    <tr>
                        <td>Paypal Address:</td>
                        <td><input type='text' name='paypal' value='{$set['paypal']}'></td>
                    </tr>
                    <tr>
                        <td>Gym &amp; Crime Validation:</td>
                        <td>
                            <select name='validate_on' type='dropdown'>
        ";

        $opt = array("1" => "On", "0" => "Off");
        foreach ($opt as $k => $v) {
            if ($k == $set['validate_on']) {
                print "<option value='{$k}' selected = 'selected'>{$v}</option>";
            } else {
                print "<option value='{$k}'>{$v}</option>";
            }
        }

        print "
                    </select>
                </td>
            </tr>
            <tr>
                <td>Validation Period:</td>
                <td><select name='validate_period' type='dropdown'>
        ";

        $opt = array("5" => "Every 5 Minutes", "15" => "Every 15 Minutes", "60" => "Every Hour", "login" => "Every Login");
        foreach ($opt as $k => $v) {
            if ($k == $set['validate_period']) {
                print "<option value='{$k}' selected='selected'>{$v}</option>";
            } else {
                print "<option value='{$k}'>{$v}</option>";
            }
        }

        print "
                    </select>
                </td>
            </tr>
            <tr>
                <td>Registration CAPTCHA:</td>
                <td>
                    <select name='regcap_on' type='dropdown'>
        ";

        $opt = array("1" => "On", "0" => "Off");
        foreach ($opt as $k => $v) {
            if ($k == $set['regcap_on']) {
                print "<option value='{$k}' selected='selected'>{$v}</option>";
            } else {
                print "<option value='{$k}'>{$v}</option>";
            }
        }

        print "
                            </select>
                        </td>
                    </tr>
                </table>
                <input type='submit' value='Update Settings'>
            </form>
        ";
    }
}

function announcements(Database $db, array $user, string $text): void
{
    if ($user['rank'] != 'Capo') {
        header("Location: home.php");
    }

    if ($text) {
        $db->query("INSERT INTO announcements (a_text, a_time) VALUES('{$text}', unix_timestamp())");
        $db->query("UPDATE users SET newAnnounce = newAnnounce + 1");

        print '
            <h3>Adding an Announcement</h3>
            <p>Announcement added!<br><a href=\'staff.php\'>Staff Home</a></p>
        ';

        staffLogAdd("Added a new announcement");
    } else {
        print '
            <h3>Adding an announcement</h3>
            <form action=\'staff.php?action=announce\' method=POST>
                <textarea name=magText rows=10 cols=75></textarea><br>
                <input type=submit value=\'Add Announcement\'>
            </form>
        ';
    }
}

function poll(Database $db, array $user): void
{
    if ($user['rank'] != 'Capo') {
        header("Location: home.php");
    }

    print "
        <h3>Create New Poll</h3>
        <p>Fill out questions and options for your new poll.</p>
        <form action='staff.php?action=startpoll' method='post'>
            Question: <input type='text' name='question' size='70'><br>
            Choice 1: &nbsp; <input type='text' name='choice1' value=''><br>
            Choice 2: &nbsp; <input type='text' name='choice2' value=''><br>
            Choice 3: &nbsp; <input type='text' name='choice3' value=''><br>
            Choice 4: &nbsp; <input type='text' name='choice4' value=''><br>
            Choice 5: &nbsp; <input type='text' name='choice5' value=''><br>
            Choice 6: &nbsp; <input type='text' name='choice6' value=''><br>
            Choice 7: &nbsp; <input type='text' name='choice7' value=''><br>
            Choice 8: &nbsp; <input type='text' name='choice8' value=''><br>
            Choice 9: &nbsp; <input type='text' name='choice9' value=''><br>
            Choice 10: <input type='text' name='choice10' value=''><br>
            <input type='submit' value='Submit'>
        </form>
        <br><br>
    ";

    if (!isset($_POST['poll'])) {
        print "
            <h3>End a Poll</h3>
            <p>Choose a poll to finish.</p>
            <form action='staff.php?action=poll' method='post'>
        ";

        $q = $db->query("SELECT id, question FROM polls WHERE active = 1");
        while ($r = mysqli_fetch_assoc($q)) {
            print "<input type='radio' name='poll' value='{$r['id']}'>Poll ID {$r['id']} - {$r['question']}<br>";
        }

        print "<br><input type='submit' value='Close Selected Poll'></form>";
    } else {
        $db->query("UPDATE polls SET active = 0 WHERE id = {$_POST['poll']}");

        print "
            <h3>Poll Ended</h3>
            <p>Poll closed.</p>
        ";
    }
}

function startpollsub(Database $db, array $user): void
{
    if ($user['rank'] != 'Capo') {
        header("Location: home.php");
    }

    print "
        <h3>Create New Poll</h3>
        <p>Starting new poll...</p>
    ";

    $question = isset($_POST['question']) ? mysql_tex($_POST['question']) : '';
    $choice1 = isset($_POST['choice1']) ? mysql_tex($_POST['choice1']) : '';
    $choice2 = isset($_POST['choice2']) ? mysql_tex($_POST['choice2']) : '';
    $choice3 = isset($_POST['choice3']) ? mysql_tex($_POST['choice3']) : '';
    $choice4 = isset($_POST['choice4']) ? mysql_tex($_POST['choice4']) : '';
    $choice5 = isset($_POST['choice5']) ? mysql_tex($_POST['choice5']) : '';
    $choice6 = isset($_POST['choice6']) ? mysql_tex($_POST['choice6']) : '';
    $choice7 = isset($_POST['choice7']) ? mysql_tex($_POST['choice7']) : '';
    $choice8 = isset($_POST['choice8']) ? mysql_tex($_POST['choice8']) : '';
    $choice9 = isset($_POST['choice9']) ? mysql_tex($_POST['choice9']) : '';
    $choice10 = isset($_POST['choice10']) ? mysql_tex($_POST['choice10']) : '';

    $db->query("INSERT into polls (active, question, choice1, choice2, choice3, choice4, choice5, choice6, choice7, choice8, choice9, choice10, hidden) VALUES(1, '{$question}', '{$choice1}', '{$choice2}', '{$choice3}', '{$choice4}', '{$choice5}', '{$choice6}', '{$choice7}', '{$choice8}', '{$choice9}', '{$choice10}', 0)");
    $db->query("UPDATE users SET pollVote = ''");

    print "
        <p>...new Poll Begun</p>
        <p><a href='staff.php'>Staff Home</a></p>
    ";
}

function mafiainquirer(Database $db, array $user, string $do, int $id, string $text): void
{
    if ($user['rankCat'] != 'Staff') {
        header("Location: home.php");
    }

    print "<h3>Mafia Inquirer Editor <span class='lighter'>&nbsp;&nbsp;&middot;&nbsp; <a href='staff.php?action=mafiainquirer&do=edit'>Create a new Article</a></span></h3>";
    if ($do == 'save') {
        #TODO: Determine POST variable types, create variables and sanitize data
        if ($id == '0') {
            $db->query("INSERT INTO newsMagazine (magAuthor, magLocation, magColumn, magVisible, magText) VALUES ({$_POST['magAuthor']}, {$_POST['magLocation']}, {$_POST['magColumn']}, '{$_POST['magVisible']}', '{$text}')");

            print "
                <p>New article created.</p>
                <p><a href='staff.php?action=mafiainquirer'>View all articles</a></p>
            ";
        } else {
            $db->query("UPDATE newsMagazine SET magAuthor = {$_POST['magAuthor']}, magLocation = {$_POST['magLocation']}, magColumn = {$_POST['magColumn']}, magVisible = '{$_POST['magVisible']}', magText = '{$text}' WHERE magID = {$id}");

            print "
                <p>Article updated.</p>
                <p><a href='staff.php?action=mafiainquirer'>View all articles</a></p>
            ";
        }
    } elseif ($do == 'edit') {
        $rmi = mysqli_fetch_assoc($db->query("SELECT magColumn, magVisible, magText FROM newsMagazine WHERE magID = {$id}"));

        print "
            <p><em>Remember, this is for posterity, so please be honest. How does this make you feel?</em></p>
            <form action='staff.php?action=mafiainquirer&do=save&id={$id}' method='POST'>
                <input type='hidden' name='magAuthor' value='{$user['userid']}'>
                Column (1 or 2) <input type='text' name='magColumn' size=2 value='{$rmi['magColumn']}'> &nbsp;
                Visible (yes or no) <input type='text' name='magVisible' size=3 value='{$rmi['magVisible']}'> &nbsp;
                Location " . locationDropdown(500, 'magLocation') . "<br>
                <textarea rows=15 cols=80 name='magText'>" . mysql_tex_edit($rmi['magText']) . "</textarea><br>
                <input type='submit' value='Submit Article'>
            </form>
            <p>
                Remember, images MUST take this form:<br>
                <pre>[img]http://www.firstmafiawar.com/images/mafioso/KefWatching.jpg[/img]</pre><br>
                If you do not use this form, the image will not work.
            </p>
        ";
    } else {
        print "<table border=0 cellspacing=0 cellpadding=2 class='table' style='font-size:smaller;'>";

        $title = "";
        $qmi = $db->query("SELECT magID, magLocation, magAuthor, magColumn, magVisible, magText FROM newsMagazine ORDER BY magLocation, magVisible");
        while ($rmi = mysqli_fetch_assoc($qmi)) {
            if ($title != locationName($rmi['magLocation'])) {
                $title = locationName($rmi['magLocation']);

                print "
                    <tr>
                        <th>{$title}</th>
                        <th>Loc</th>
                        <th>Visible</th>
                        <th>Content</th>
                    </tr>
                ";
            }

            print "
                <tr>
                    <td valign='top'>" . mafiosoLight($rmi['magAuthor']) . "<br><a href='staff.php?action=mafiainquirer&do=edit&id={$rmi['magID']}'><em>Edit</em></a></td>
                    <td valign='top'>{$rmi['magColumn']}</td>
                    <td valign='top'>{$rmi['magVisible']}</td>
                    <td>" . mysql_tex_out($rmi['magText']) . "</td>
                </tr>
                <tr><td colspan=4 style='border-bottom: dashed 1px rgb(153,153,153);'>&nbsp;</td></tr>
            ";
        }

        print "</table>";
    }
}

function massmailer(Database $db, array $user, int $userId, string $text): void
{
    if ($user['rank'] != 'Capo') {
        header("Location: home.php");
    }

    if ($text) {
        print "<h3>Mass Mailer</h3>";

        $text = nl2br(strip_tags($text));
        $subj = "Administrative Mailing";
        if ($_POST['cat'] == 1) {
            $query = $db->query("SELECT userid, username FROM users WHERE rankCat = 'Player' AND rankCat = 'Staff'");
        } else if ($_POST['cat'] == 2) {
            $query = $db->query("SELECT userid, username FROM users WHERE rankCat = 'Staff'");
        }

        while ($row = mysqli_fetch_assoc($query)) {
            $db->query("INSERT INTO mail (mail_read, mail_from, mail_to, mail_time, mail_subject, mail_text, mail_directory) VALUES (0, $userId, {$row['userid']}, unix_timestamp(), '{$subj}', '{$text}', 'Inbox')");
            print "Mail sent to {$row['username']}.<br>";
        }

        print "Mass mail complete!<br><a href='staff.php'>Staff Home</a>";
    } else {
        print "
            <h3>Mass Mailer</h3>
            <form action='staff.php?action=massmailer' method='post'>
                <p>
                    Send to:<br>
                    <input type='radio' name='cat' value='1'> All non Giovane
                    <input type='radio' name='cat' value='2'> Staff Only
                </p>
                <textarea name='text' rows='7' cols='60'></textarea><br>
                <input type='submit' value='Send Mail'>
            </form>
        ";
    }
}

function serverinfo(array $user): void
{
    if ($user['rank'] != 'Capo') {
        header("Location: home.php");
    }

    echo phpinfo();
}

function index(Database $db, array $user, array $set, string $pad): void
{
    if ($user['rankCat'] != 'Staff') {
        header("Location: home.php");
    }

    $pv = phpversion();
    $mv = mysqli_fetch_array($db->query("SELECT VERSION()"))[0];

    print "<h4>Staff Notepad</h4>";
    if ($pad) {
        $db->query("UPDATE settings SET conf_value = '{$pad}' WHERE conf_name = 'staff_pad'");
        $set['staff_pad'] = stripslashes($pad);
    }

    print "
        <form action='staff.php' method='post'>
            <textarea rows='5' cols='80' name='pad'>" . htmlspecialchars($set['staff_pad']) . "</textarea><br>
            <input type='submit' value='Update Notepad'>
        </form>
        <br><hr>
    ";

    print "
        <h4>Recent Staff Actions <span class='light'><a href='staffLogs.php?action=stafflogs'>(see full list)</a></span></h4>
        <table width='95%' cellspacing='0' cellpadding='2' class='table'>
            <tr>
                <th>Staff</th>
                <th>Action</th>
                <th>Time</th>
                <th>IP</th>
            </tr>
    ";

    $query = $db->query("SELECT s.user, s.action, s.time, s.ip, u.userid, u.username FROM stafflog AS s LEFT JOIN users AS u ON s.user = u.userid ORDER BY s.time DESC LIMIT 15");
    while ($row = mysqli_fetch_assoc($query)) {
        print "
            <tr>
                <td>{$row['username']} [{$row['user']}]</td>
                <td>{$row['action']}</td>
                <td>" . date('F j Y g:i:s a', $row['time']) . "</td>
                <td>{$row['ip']}</td>
            </tr>
        ";
    }

    print "
        </table><br>
        <hr>
        <h4>System Info
    ";

    if ($user['rank'] == 'Capo') {
        print " <span class='light'><a href='staff.php?action=serverinfo'>(see full details)</a></span>";
    }

    print "
        </h4>
        <table cellspacing='0' cellpadding='3' class='table'>
            <tr><td>PHP Version:</td><td>{$pv}</td></tr>
            <tr><td>MySQL Version:</td><td>{$mv}</td></tr>
        </table>
    ";
}

function street_fight(Database $db, string $text, int $min, int $max, int $start, int $end, int $class, int $grand, int $gift): void
{
    if ($end > 1) {
        print "
            <h3>Start a Street Fight</h3>
            <p>Set up new street fights here.</p>
        ";

        $db->query("INSERT INTO streetFight (sfTitle, sfLevelMin, sfLevelMax, sfStart, sfEnd, sfClass, sfPrize, sfPrizeWinner, sfGift, sfComment) VALUES ('{$text}', {$min}, {$max}, {$start}, {$end}, {$class}, {$grand}, '', {$gift}, '')");
        print '<p>You have successfully started a new street fight!</p><p><a href=\'streetfight.php\'>Check it out</a> or <a href=\'staff.php?action=streetfight\'>return to set another</a>.</p>';
    } else {
        print '
            <table border=0 cellpadding=3 cellspacing=0 class=table>
                <tr>
                    <th>Title</th>
                    <th>Level Range</th>
                    <th>Start</th>
                    <th>Duration</th>
                    <th>Prize</th>
                    <th>Gift</th>
                </tr>
        ';

        $qsf = $db->query("SELECT sfTitle, sfLevelMax, sfLevelMin, sfStart, sfEnd, sfPrize, sfGift FROM streetFight WHERE sfEnd > 0 ORDER BY sfStart");
        while ($rsf = mysqli_fetch_assoc($qsf)) {
            print '<tr><td>' . $rsf['sfTitle'] . '</td><td align=center><span class=light>level ' . $rsf['sfLevelMin'] . '-' . $rsf['sfLevelMax'] . '</span></td><td align=center>' . $rsf['sfStart'] . ' hrs</td><td align=center>' . $rsf['sfEnd'] . ' hrs</td><td>' . itemInfo($rsf['sfPrize']) . '</td><td>' . itemInfo($rsf['sfGift']) . '</td></tr>';
        }

        print '
            </table><br><br><br>
            <form action=\'staff.php?action=streetfight\' method=POST>
                <table width=50% cellpadding=2 cellspacing=0 class=table>
                    <tr><th colspan=2>Start a fight</th></tr>
                    <tr>
                        <td>Title</td>
                        <td><input type=text size=25 name=magText value=\'Why?\'></td>
                    </tr>
                    <tr>
                        <td>Min Level<br><span class=light> &nbsp; (1, 50, 100)</span></td>
                        <td><input type=text size=25 name=min value=1></td>
                    </tr>
                    <tr>
                        <td>Max Level<br><span class=light> &nbsp; (49, 99, 5000)</span></td>
                        <td><input type=text size=25 name=max value=49></td>
                    </tr>
                    <tr>
                        <td>Hours until Start</td>
                        <td><input type=text size=25 name=start value=\'number of hours until start\'></td>
                    </tr>
                    <tr>
                        <td>Duration</td>
                        <td><input type=text name=end size=25 value=\'hours the fight will last\'></td>
                    </tr>
                    <tr>
                        <td>Grand Prize</td>
                        <td>
                            <select name=grand type=dropdown>
        ';

        $query = $db->query("SELECT itmid, itmname FROM items WHERE itmtype < 50 ORDER BY itmLevel, itmname");
        while ($row = mysqli_fetch_assoc($query)) {
            print '<option value=\'' . $row['itmid'] . '\'>' . $row['itmname'] . '</option>';
        }

        print "
                    </select>
                </td>
            </tr>
            <tr>
                <td>Runner up Prizes</td>
                <td>
                    <select name=gift type=dropdown>
        ";

        $query = $db->query("SELECT itmid, itmname FROM items WHERE itmLevel > 4 AND itmLevel < 10 ORDER BY itmLevel, itmname");
        while ($row = mysqli_fetch_assoc($query)) {
            print '<option value=\'' . $row['itmid'] . '\'>' . $row['itmname'] . '</option>';
        }

        print "
                            </select>
                        </td>
                    </tr>
                    <tr><td colspan=2><input type='submit' name='class' value='Create the Fight'></td></tr>
                </table>
            </form>
        ";
    }
}

$application->header->endPage();
