<?php

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$by = isset($_GET['by']) ? mysql_tex($_GET['by']) : 'username';
$st = isset($_GET['st']) ? mysql_num($_GET['st']) : 0;
$ord = isset($_GET['ord']) ? mysql_tex($_GET['ord']) : 'ASC';
$active = isset($_GET['active']) ? mysql_num($_GET['active']) : 0;
$attack = isset($_GET['attack']) ? mysql_num($_GET['attack']) : 0;
$giovane = isset($_GET['giovane']) ? mysql_num($_GET['giovane']) : 0;
$location = isset($_GET['location']) ? mysql_num($_GET['location']) : 0;
$name = isset($_GET['name']) ? mysql_tex($_GET['name']) : '';
$online = isset($_GET['online']) ? mysql_tex($_GET['online']) : '';
$membs = 0;

// options
if ($active) {
    $options = " WHERE `rank` IN ('Mafioso', 'Don')";
    $addtitle = 'Active Players';
    $loc = "active = 1";
} else if ($attack) {
    $attupper = ($application->user['comRank'] + 20);
    $attlower = max(0, ($application->user['comRank'] - 20));
    $options = " WHERE location = {$application->user['location']} AND trackActionTime < unix_timestamp() - 15 * 60 AND hospital = 0 AND jail = 0 AND rankCat != 'Staff' AND (`rank` IN ('Associate', 'Giovane') OR comRank BETWEEN {$attlower} AND {$attupper})";
    $addtitle = 'Attack Targets';
    $loc = "attack = 1";
} else if ($giovane) {
    $options = " WHERE `rank` = 'Giovane'";
    $addtitle = 'Giovane';
    $loc = "giovane = 1";
} else if ($location) {
    $options = " WHERE location = {$application->user['location']} AND rankCat != 'Staff'";
    $addtitle = 'Location';
    $loc = "location = {$application->user['location']}";
} else if ($name) {
    $options = " WHERE username LIKE ('%{$name}%') AND rankCat != 'Staff'";
    $addtitle = 'Name Search: ' . $name;
    $loc = "name = {$name}";
} else if ($online) {
    $time = time() - 15 * 60;
    $options = " WHERE trackActionTime >= {$time} AND rankCat = 'Player' ";
    $addtitle = 'Online';
    $loc = "online = 1";
} else {
    $options = "WHERE rankCat != 'Staff'";
    $addtitle = 'Everyone';
    $loc = "everyone";
}

print '
    <h3>Mafioso Results <span class=lighter>&nbsp;&middot;&nbsp; ' . $addtitle . '</h3>
    <a href=\'mafiosoSearch.php\'>General Search</a> &nbsp; &middot; &nbsp;
    <a href=\'mafiosoResults.php?attack=1\'>Attack Targets</a> &nbsp; &middot; &nbsp;
    <a href=\'mafiosoResults.php?giovane=1\'>Giovane</a> &nbsp; &middot; &nbsp;
    <a href=\'mafiosoResults.php\'>Everyone</a> &nbsp; &middot; &nbsp;
    <a href=\'mafiosoResults.php?active=1\'>Active</a> &nbsp; &middot; &nbsp;
    <a href=\'mafiosoResults.php?online=1\'>Online</a> &nbsp; &middot; &nbsp;
';

// Database query
$query = $application->db->query("SELECT userid, comRank, level, money, respect, location, trackActionTime, username FROM users {$options} ORDER BY {$by} {$ord} LIMIT {$st}, 50");
$q2 = $application->db->query("SELECT userid FROM users {$options}");
$membs = mysqli_num_rows($q2);

// Page count
$pages = (int)($membs / 50) + 1;
print " <div class='floatright'>pages: ";
for ($i = 1; $i <= $pages; $i++) {
    $stl = ($i - 1) * 50;
    if ($stl == $st) {
        print "<strong>{$i}</strong>&nbsp;";
    } else {
        print "<a href='mafiosoResults.php?{$loc}&st={$stl}&by={$by}&ord={$ord}'>{$i}</a>&nbsp;";
    }
}
print "</div>";

$no1 = $st + 1;
$no2 = $st + 50;
print '
    <table width=95% cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>
        <tr>
            <td colspan=5>Showing ' . $no1 . ' to ' . $no2 . ' of ' . $membs . '.</td>
            <td colspan=3 style=\'text-align:right;\'><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=' . $by . '&ord=asc\'>Ascending</a> or <a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=' . $by . '&ord=desc\'>Descending</a>.</td>
        </tr>
        <tr>
            <th style=\'text-align:left;\'><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=username&ord=ASC\'>Name</a> <a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=userid&ord=ASC\'>(ID)</a></th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=level&ord=DESC\'>Level</a></th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=comRank&ord=DESC\'>Rank</a></th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=money&ord=DESC\'>Money</a> &nbsp; </th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=respect&ord=DESC\'>Respect</a></th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=location&ord=ASC\'>Location</a></th>
            <th><a href=\'mafiosoResults.php?' . $loc . '&st=' . $st . '&by=trackActionTime&ord=DESC\'>Status</a></th>
            <th>Action</th>
        </tr>
';

while ($row = mysqli_fetch_assoc($query)) {
    print '
        <tr>
            <td>' . mafioso($row['userid']) . '</td>
            <td class=center>' . $row['level'] . '</td>
            <td class=center>' . $row['comRank'] . '</td>
            <td style=\'text-align:right;\'>' . moneyFormatter($row['money']) . '</td>
            <td class=center>' . $row['respect'] . '</td>
            <td class=center>' . locationName($row['location']) . '</td>
            <td class=center>' . status($row['userid']) . '</td>
            <td class=center><a href=\'mailbox.php?action=compose&ID=' . $row['userid'] . '\'>mail</a> &nbsp;&middot;&nbsp; <a href=\'attack.php?ID=' . $row['userid'] . '\'>attack</a></td>
        </tr>
    ';
}

print '</table>';

$application->header->endPage();
