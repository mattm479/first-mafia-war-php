<?php

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 1);

$cstart = isset($_GET['cstart']) ? mysql_num($_GET['cstart']) : 0;

// if you signed up for a new course get it going
if ($cstart) {
    print '<h3>Meet your Mentor</h3>';

    $cd = $application->db->query("SELECT crCOST, crDAYS FROM courses WHERE crID = {$cstart}");
    if (mysqli_num_rows($cd) == 0) {
        print '
            <p>You are trying to learn nothing. That\'s hard to do and not much good to a Mafioso.</p>
            <p><a href=\'education.php\'>Try again</a></p>
        ';

        $application->header->endPage();
        exit;
    }

    $coud = mysqli_fetch_assoc($cd);
    $cdo = $application->db->query("SELECT userid, courseid FROM coursesdone WHERE userid = {$userId} AND courseid = {$cstart}");
    if ($application->user['money'] < $coud['crCOST']) {
        print '<p>You can\'t find any decent mentors with that kind of money. <a href=\'crime.php\'>Go get more</a> or <a href=\'education.php\'>Try for a lesser mentor</a>.</p>';

        $application->header->endPage();
        exit;
    }

    if (mysqli_num_rows($cdo) > 0) {
        print '
            <p>You have already learned this.</p>
            <p><a href=\'education.php\'>Would you like to try another</a>?
        ';

        $application->header->endPage();
        exit;
    }

    $application->db->query("UPDATE users SET course = {$cstart}, cdays = {$coud['crDAYS']}, money = money - {$coud['crCOST']} WHERE userid = {$userId}");
    $days = $coud['crDAYS'];
    print '<p>You have begun your training.</p>';
}

// find your current course
$cd = $application->db->query("SELECT crNAME FROM courses WHERE crID = {$application->user['course']}");
$coud = mysqli_fetch_assoc($cd);

print '<h3>Learn from the best</h3>';
if ($application->user['course'] > 0) {
    print '<p>You are currently working on <em>' . $coud['crNAME'] . '</em>.<br>It will be another ' . $application->user['cdays'] . ' days before you will know anything useful.</p>';
} else {
    print '<p>Find a mentor who can teach you from the list below by clicking on <strong>Learn</strong>.<br>If you have the cash, in just a few days you will be better than you are now with a little help!<br>When you have completed this list, another shall be made available and you can learn more powerful abilities.</p>';
}

print '
    <table width=95% cellspacing=0 cellspacing=3 class=table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th style=\'text-align:center;\'>Cost</th>
            <th style=\'text-align:center;\'>Days</th>
            <td></td>
        </tr>
';

$ct = $application->db->query("SELECT courseid FROM coursesdone WHERE userid = {$userId}");
if (mysqli_num_rows($ct) <= 13) {
    $query = $application->db->query("SELECT crID, crNAME, crDESC, crBENE, crCOST, crDAYS FROM courses WHERE crID < 20 ORDER BY crDAYS");
} else {
    $query = $application->db->query("SELECT crID, crNAME, crDESC, crBENE, crCOST, crDAYS FROM courses WHERE crID > 19 ORDER BY crDAYS");
}

while ($row = mysqli_fetch_assoc($query)) {
    $cdo = $application->db->query("SELECT userid, courseid FROM coursesdone WHERE userid = {$userId} AND courseid = {$row['crID']}");
    if (mysqli_num_rows($cdo)) {
        $do = '<span class=light>Done</span>';
    } else if ($application->user['course'] == $row['crID']) {
        $do = '<strong>' . $application->user['cdays'] . ' days</strong>';
    } else if ($application->user['course'] > 0) {
        $do = '<span class=light>Available</span>';
    } else {
        $do = '<a href=\'education.php?cstart=' . $row['crID'] . '\'>Learn</a>';
    }

    print '
        <tr>
            <td valign=top>' . $row['crNAME'] . '</td>
            <td>' . $row['crDESC'] . '<br><span class=light>' . $row['crBENE'] . '</span></td>
            <td style=\'text-align:right;\'>' . moneyFormatter($row['crCOST']) . '</td>
            <td style=\'text-align:center;\'>' . $row['crDAYS'] . '</td>
            <td style=\'text-align:center;\'>' . $do . '</td>
        </tr>
    ';
}

print '</table>';

$application->header->endPage();
