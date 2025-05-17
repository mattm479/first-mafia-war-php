<?php

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0);

$st = isset($_GET['st']) ? mysql_num($_GET['st']) : 0;
$by = isset($_GET['by']) ? mysql_tex($_GET['by']) : '';

// options
$q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' ORDER BY level DESC LIMIT 30");
$addtitle = 'Highest Level';

if ($by == "cash") {
    $q = $application->db->query("SELECT userid, level, money, respect, location, sum(money + moneyChecking) AS sumWealth FROM users WHERE rankCat = 'Player' GROUP BY userid ORDER BY sumWealth DESC LIMIT 30");
    $addtitle = 'Most Cash &amp; Checking';
} else if ($by == "wealth") {
    $q = $application->db->query("SELECT userid, level, money, respect, location, sum(money + moneyChecking + moneySavings + moneyInvest + moneyTreasury) AS sumWealth FROM users WHERE rankCat = 'Player' GROUP BY userid ORDER BY sumWealth DESC LIMIT 30");
    $addtitle = 'Greatest Financial Wealth';
} else if ($by == "respect") {
    $q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' ORDER BY respect DESC LIMIT 30");
    $addtitle = 'Most Tokens of Respect';
} else if ($by == "enemies") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location, count(l.clID) AS countValue FROM users u LEFT JOIN contactList l ON u.userid = l.clContact WHERE clType = 'enemy' AND rankCat = 'Player' GROUP BY clContact ORDER BY countValue DESC LIMIT 30");
    $addtitle = 'Most Enemies';
} else if ($by == "friends") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location, count(l.clID) AS countValue FROM users u LEFT JOIN contactList l ON u.userid = l.clContact WHERE clType = 'friend' AND rankCat = 'Player' GROUP BY clContact ORDER BY countValue DESC LIMIT 30");
    $addtitle = 'Most Friendships';
} else if ($by == "strength") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.rankCat != 'Staff' ORDER BY us.strength DESC LIMIT 30");
    $addtitle = 'Highest Strength';
} else if ($by == "agility") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.rankCat != 'Staff' ORDER BY us.agility DESC LIMIT 30");
    $addtitle = 'Highest Agility';
} else if ($by == "IQ") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.rankCat != 'Staff' ORDER BY us.IQ DESC LIMIT 30");
    $addtitle = 'Highest IQ';
} else if ($by == "labour") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.rankCat != 'Staff' ORDER BY us.labour DESC LIMIT 30");
    $addtitle = 'Highest Labour';
} else if ($by == "guard") {
    $q = $application->db->query("SELECT u.userid, u.level, u.money, u.respect, u.location FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.rankCat != 'Staff' ORDER BY us.guard DESC LIMIT 30");
    $addtitle = 'Highest Guard';
} else if ($by == "bust") {
    $q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' AND jailBusts > 0 ORDER BY jailBusts DESC LIMIT 30");
    $addtitle = 'Most Jail Busts Today';
} else if ($by == "bribe") {
    $q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' AND jailBails > 0 ORDER BY jailBails DESC LIMIT 30");
    $addtitle = 'Most Jail Bails Today';
} else if ($by == "hospitler") {
    $q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' AND count_hospital > 0 ORDER BY count_hospital DESC LIMIT 30");
    $addtitle = 'Today\'s Top Hospitalers';
} else if ($by == "jailer") {
    $q = $application->db->query("SELECT userid, level, money, respect, location FROM users WHERE rankCat = 'Player' AND count_jail > 0 ORDER BY count_jail DESC LIMIT 30");
    $addtitle = 'Today\'s Top Jailers';
}

// title
print '
    <h3>' . $addtitle . '</h3>
    <p>
        <a href=\'statisticsTopMafia.php?by=cash\'>Cash</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=wealth\'>Wealth</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=respect\'>Respect</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=strength\'>Strength</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=agility\'>Agility</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=guard\'>Guard</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=labour\'>Labour</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=IQ\'>IQ</a> <br>
        
        <a href=\'statisticsTopMafia.php\'>Level</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=enemies\'>Enemies</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=friends\'>Friends</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=bust\'>Jail Busts</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=bribe\'>Jail Bribes</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=hospitler\'>Hospitler</a> &nbsp;&middot;&nbsp;
        <a href=\'statisticsTopMafia.php?by=jailer\'>Jailer</a>
    </p>
    <table width=95% cellspacing=0 cellpadding=2 class=table>
        <tr>
            <th style=\'text-align:left;\'>Name</th>
            <th style=\'text-align:right;\'>Level</th>
            <th style=\'text-align:right;\'>Cash</th>
            <th>Respect</th>
            <th>Location</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
';

while ($r = mysqli_fetch_assoc($q)) {
    print '
        <tr>
            <td style=\'font-size:smaller;\'>' . mafioso($r['userid']) . '</td>
            <td style=\'text-align:right; font-size:smaller;\'>' . $r['level'] . '</td>
            <td style=\'text-align:right; font-size:smaller;\'>$' . number_format($r['money']) . '</td>
            <td style=\'text-align:center; font-size:smaller;\'>' . $r['respect'] . '</td>
            <td style=\'text-align:center; font-size:smaller;\'>' . locationName($r['location']) . '</td>
            <td style=\'text-align:center; font-size:smaller;\'>' . status($r['userid']) . '</td>
            <td style=\'text-align:center; font-size:smaller;\'>
    ';

    if ($r['userid'] == $userId) {
        print '<strong>Congratulations!</strong>';
    } else {
        print '<a href=\'mailbox.php?action=compose&ID=' . $r['userid'] . '\'>mail</a> &nbsp;&middot;&nbsp; <a href=\'attack.php?ID=' . $r['userid'] . '\'>attack</a>';
    }

    print '</td></tr>';
}

print '</table>';

$application->header->endPage();
