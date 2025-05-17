<?php

use Fmw\Database;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : "";
$do = isset($_GET['do']) ? mysql_num($_GET['do']) : 0;

print '
    <h3>Street Fight</h3>
    <div class=floatright>
        <img src=\'assets/images/photos/streetFight.jpg\' width=350 height=234 alt=\'Street Fight\'><br>
        <table width=340 cellpadding=2 cellspacing=0 class=table style=\'font-size:smaller;\'>
';

$qcsf = $application->db->query("SELECT sfID, sfLevelMin, sfLevelMax, sfTitle FROM streetFight WHERE sfStart = 0 AND sfEnd > 0 ORDER BY sfEnd ");
while ($rcsf = mysqli_fetch_assoc($qcsf)) {
    $join = '';
    if ($application->user['attacksID'] == 0 && $application->user['level'] >= $rcsf['sfLevelMin'] && $application->user['level'] <= $rcsf['sfLevelMax']) {
        $join = '<a href=\'streetfight.php?action=join&do=' . $rcsf['sfID'] . '\'>join&rang;</a>';
    }

    $qcf = $application->db->query("SELECT userid, attacks FROM users WHERE attacksID = {$rcsf['sfID']} ORDER BY attacks DESC");
    print '
        <tr><td colspan=3><br></td></tr>
        <tr>
            <th>' . $join . '</th>
            <th style=\'text-align:left;\'>' . $rcsf['sfTitle'] . ' <span class=light>(level ' . $rcsf['sfLevelMin'] . '-' . $rcsf['sfLevelMax'] . ')</span></th>
            <th style=\'text-align=right;\'>Score</th>
        </tr>
    ';

    while ($rcf = mysqli_fetch_assoc($qcf)) {
        print '
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;' . mafioso($rcf['userid']) . '</td>
                <td align=center>' . number_format($rcf['attacks']) . '</td>
            </tr>
        ';
    }
}

print '</table></div>';

switch ($action) {
    case 'join':
        join_fight($application->db, $application->user, $userId, $do);
        break;
    default:
        index($application->db);
        break;
}

function index(Database $db): void
{
    $qcsf = $db->query("SELECT sfTitle, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfStart = 0 AND sfEnd > 0");
    $qnsf = $db->query("SELECT sfTitle, sfStart, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfStart > 0 ORDER BY sfStart LIMIT 3");
    $qpsf = $db->query("SELECT sfTitle, sfComment FROM streetFight WHERE sfEnd = 0 ORDER BY sfID DESC LIMIT 3");

    print '
        <p>Street fights can last anywhere from a few hours to days. You can only be in one street fight at a time and fights are limited by level so you cannot be in all fights. At the end of the fight, the top fighter gets the Grand Prize and the top three fighters gain a smaller gift.</p>
        <p>During the combat period, all fights you win get you two points. You lose one point for losing a fight, and you lose three points for picking up a dagger (so if you get a dagger - AND lose, you lose four points). The fighter with the most points at the end of the fight wins.</p>
    ';

    print '&rang; <em>Current Fights</em><br>';
    while ($rcsf = mysqli_fetch_assoc($qcsf)) {
        print '<p><strong>' . $rcsf['sfTitle'] . '</strong> <span class=light>(level ' . $rcsf['sfLevelMin'] . '-' . $rcsf['sfLevelMax'] . ')</span> <br>This fight will last ' . $rcsf['sfEnd'] . ' more hours. The Grand Prize is a ' . itemInfo($rcsf['sfPrize']) . ', the three top fighters each get a ' . itemInfo($rcsf['sfGift']) . '.</p>';
    }

    print '&rang; <em>Upcoming Fights</em><br>';
    while ($rnsf = mysqli_fetch_assoc($qnsf)) {
        if ($rnsf['sfStart'] == 1) {
            $start = 'at the top of the hour';
        } else {
            $start = 'in ' . $rnsf['sfStart'] . ' hours';
        }

        print '<p><strong>' . $rnsf['sfTitle'] . '</strong> <span class=light>(level ' . $rnsf['sfLevelMin'] . '-' . $rnsf['sfLevelMax'] . ')</span> <br>This fight begins ' . $start . ' and will last ' . $rnsf['sfEnd'] . ' more hours. The Grand Prize is a ' . itemInfo($rnsf['sfPrize']) . ', the three top fighters each get a ' . itemInfo($rnsf['sfGift']) . '.</p>';
    }

    print '&rang; <em>Recent Fights</em><br>';
    while ($rpsf = mysqli_fetch_assoc($qpsf)) {
        print '<strong>' . $rpsf['sfTitle'] . '</strong><br><p>' . $rpsf['sfComment'] . '</p>';
    }
}

function join_fight(Database $db, array $user, int $userId, int $do): void
{
    $query = $db->query("SELECT sfTitle, sfEnd, sfPrize, sfGift, sfLevelMin, sfLevelMax FROM streetFight WHERE sfID = {$do}");
    $row = mysqli_fetch_assoc($query);

    if ($row['sfLevelMin'] <= $user['level'] && $row['sfLevelMax'] >= $user['level']) {
        $db->query("UPDATE users SET attacks = 1, attacksID = {$do} WHERE userid = {$userId}");

        print '
            <p>OK, you\'re in it now.  Do not move slowly, you do not want to lose!</p>
            <p><a href=\'mafiosoResults.php?attack=1\'>Find a Fight</a></p>
        ';
    } else {
        print '
            <p>You cannot fight in this fight. Fights are arranged by classes around what sorts of gear and equipment folks can get their hands on. This fight is outside your class.</p>
            <p><a href=\'streetFight.php\'>Please try another</a>.</p>
        ';
    }
}

$application->header->endPage();
