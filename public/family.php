<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$myFamilyId = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$dId = isset($_GET['did']) ? mysql_num($_GET['did']) : 0;
$familyId = isset($_GET['fid']) ? mysql_num($_GET['fid']) : 0;
$iId = isset($_GET['iid']) ? mysql_num($_GET['iid']) : 0;
$mafiosoId = isset($_GET['mid']) ? mysql_num($_GET['mid']) : 0;
$name = isset($_POST['name']) ? mysql_tex($_POST['name']) : '';
$description = isset($_POST['desc']) ? mysql_tex($_POST['desc']) : '';
$location = isset($_POST['loca']) ? mysql_num($_POST['loca']) : 0;

switch ($action) {
    case 'apps':
        family_applications($application->db, $application->user, $userId, $dId, $familyId, $mafiosoId);
        break;
    case 'create':
        family_create($application->db, $application->header, $application->user, $userId, $location, $name, $description);
        break;
    case 'view':
        family_view($application->db, $application->header, $userId, $myFamilyId);
        break;
    case 'list':
    default:
        family_list($application->db, $userId);
        break;
}

function family_applications(Database $db, array $user, int $userId, int $dId, int $familyId, int $mafiosoId): void
{
    print '<h5>Family Invitations</h5>';

    if ($dId == 1 && $user['gangrank'] == $familyId) {
        $mafiosoName = mafioso($mafiosoId);

        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$familyId}, unix_timestamp(), '{$mafiosoName} refused your invitation.');");
        $db->query("UPDATE users SET gangrank = 0 WHERE userid = {$mafiosoId}");

        print '
            <p>You successfully refused the invitation.</p>
            <p><a href=\'home.php\'>Home</a></p>
        ';
    } else if ($dId == 2 && $user['gangrank'] == $familyId) {
        $mafiosoName = mafioso($mafiosoId);

        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES ({$familyId}, unix_timestamp(), '{$mafiosoName} accepted your invitation.');");
        $db->query("UPDATE users SET gang = {$familyId}, daysingang = 1, gangrank = 7 WHERE userid = {$userId}");

        print '
            <p>You successfully accepted the invitation.</p>
            <p><a href=\'home.php\'>Home</a></p>
        ';
    } else {
        print '<p>There seems to be some confusion. Either you have already accepted or declined the invitation, or this Family has withdrawn the offer.</p>';
    }
}

function family_create(Database $db, Header $headers, array $user, int $userId, int $location, string $name, string $description): void
{
    $crsq = $db->query("SELECT courseid, userid FROM coursesdone WHERE courseid = 20 AND userid = {$userId}");
    if (mysqli_num_rows($crsq) == 0) {
        print '
            <h3>Create Family</h3>
            <p>You do not have the experience and training required to start a family. Continue with your mentors and watch for new options.</p>
            <p><a href=\'home.php\'>Go home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($user['money'] < 5000000) {
        print '
            <h3>Create Family</h3>
            <p>Your money is not long enough to create your own Family. You cannot afford to feed the little ones.</p>
            <p><a href=\'home.php\'>Go home</a>.</p>
        ';

        $headers->endpage();
        exit();
    }

    if ($user['gang']) {
        print '
            <h3>Create Family</h3>
            <p>You cannot found a Family while in a Family currently. Bad form.</p>
            <p><a href=\'home.php\'>Go home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    if ($name != '') {
        $db->query("INSERT INTO family (famName, famDesc, famDescInt, famTag, famDon, famSize, famRespect, famVaultCash, famVaultTokens, famHeadquarters, famLockdown, famCoatOfArms, famMugShot) VALUES('{$name}', '{$description}', '', '', {$userId}, 10, 100, 0, 0, {$location}, '', 'genericCoat.jpg', '')");
        $id = mysqli_insert_id($db);

        $db->query("INSERT INTO forum (foTitle, foSubtitle, foView, foDeleted) VALUES('{$name}', 'Private Family Discussions', {$id}, 0)");
        itemAdd(12, 4, 0, 0, $id);
        itemAdd(13, 2, 0, 0, $id);
        itemAdd(67, 1, 0, 0, $id);
        itemAdd(24, 1, 0, 0, $id);
        itemAdd(71, 1, 0, 0, $id);
        itemAdd(27, 4, 0, 0, $id);
        itemAdd(26, 2, 0, 0, $id);
        itemAdd(66, 1, 0, 0, $id);
        itemAdd(54, 1, 0, 0, $id);
        itemAdd(71, 1, 0, 0, $id);
        itemAdd(46, 2, 0, 0, $id);

        $db->query("UPDATE users SET gang = {$id}, money = money - 5000000, gangtitle = 'Don', gangrank = 1, `rank` = 'Don' WHERE userid = {$userId}");
        print '
            <h3>Family Created</h3>
            <p>Congratulations!</p>
            <p><a href=\'familyYours.php\'>Visit the Family Home</a></p>
        ';
    } else {
        print '
            <h3>Create A Family</h3>
            <form action=\'family.php?action=create\' method=POST>
                <input type=hidden name=submit value=1>
                Family Name:<input type=text name=name> and headquartered in: ' . locationDropdown($user['level'], 'loca') . '<br><br>
                <textarea name=desc cols=60 rows=6></textarea><br>
                <input type=submit value=\'Create Family for $5,000,000\'>
            </form>
        ';
    }
}

function family_list(Database $db, int $userId): void
{
    print '
        <h3>Family Table</h3>
        <div class=floatright>
            <img src=\'assets/images/photos/family.jpg\' width=229 height=231 alt=\'Family Gathering\'><br><br>
            <h5>Family Wars</h5>
    ';

    $wq = $db->query("SELECT famWarID, famWarAtt, famWarAttPoints, famWarType, famWarDef, famWarDefPoints FROM familyWar WHERE famWarEnd = 0");
    if (mysqli_num_rows($wq) == 0) {
        print '
                <p>The world is in a state of relative peace.</p>
                <p>There are no Family wars in progress.</p>
            </div>
        ';
    } else {
        print '
            <table width=230 cellpadding=2 cellspacing=0 class=table style=\'font-size:smaller\'>
                <tr>
                    <th style=\'text-align:center;\'>Attacker</th>
                    <th>&nbsp;</th>
                    <th style=\'text-align:center;\'>Defender</th>
                </tr>
        ';

        while ($row = mysqli_fetch_assoc($wq)) {
            print '
                <tr>
                    <td class=center>' . familyName($row['famWarAtt']) . '<br>' . $row['famWarAttPoints'] . '</td>
                    <td class=center>' . warType($row['famWarType']) . '<br>(' . ($row['famWarType'] * 10 + 10) . ')</td>
                    <td class=center>' . familyName($row['famWarDef']) . '<br>' . $row['famWarDefPoints'] . '</td>
                </tr>
            ';
        }

        print '
                </table>
            </div>
        ';
    }

    print '<p>Once you have proven yourself to yourself, you should consider the support and benefits of a Family. You can create your own for $5,000,000 if you have the right training. To join an established Family, talk to their members and hope for an invitation.</p>';
    $crsq = $db->query("SELECT courseid, userid FROM coursesdone WHERE courseid = 20 AND userid = {$userId}");
    if (mysqli_num_rows($crsq) > 0) {
        print '<p><a href=\'family.php?action=create\'><strong>Create your own Family</strong></a></p>';
    } else {
        print '<p>You do not yet have the skills needed to create your own Family. Consider joining one of the fine Families listed here.</p>';
    }

    print '
        <table width=60% cellspacing=0 cellpadding=3 class=table>
            <tr>
                <th>Family</th>
                <th style=\'text-align:center;\'>Home</th>
                <th style=\'text-align:center;\'>Members</th>
                <th style=\'text-align:center;\'>Respect</th>
            </tr>
    ';

    $gq = $db->query("SELECT famID, famName, famHeadquarters, famRespect FROM family WHERE famID > 1 AND famRespect > 0 ORDER BY famRespect DESC;");
    while ($familyData = mysqli_fetch_assoc($gq)) {
        $cnt = $db->query("SELECT userid FROM users WHERE gang = {$familyData['famID']}");
        print '
            <tr>
                <td><a href=\'family.php?action=view&ID=' . $familyData['famID'] . '\'>' . $familyData['famName'] . '</a></td>
                <td style=\'text-align:center;\'>' . locationName($familyData['famHeadquarters']) . '</td>
                <td style=\'text-align:center;\'>' . mysqli_num_rows($cnt) . '</td>
                <td style=\'text-align:center;\'>' . $familyData['famRespect'] . '</td>
            </tr>
        ';
    }

    print '</table>';
}

function family_view(Database $db, Header $headers, int $userId, int $myFamilyId): void
{
    if ($myFamilyId == 0) {
        print '
            <h3>Family Business</h3>
            <p>My apologies, but you cannot look at this family.</p>
            <p><a href=\'explore.php\'>Back to town</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $familyData = mysqli_fetch_assoc($db->query("SELECT famID, famName, famCoatOfArms, famHeadquarters, famRespect, famDesc, famDon FROM family WHERE famID = {$myFamilyId}"));

    print '
        <h3>' . $familyData['famName'] . ' Family</h3>
        <div class=floatrightbox style=\'margin-left:1em;\'>
            <table width=95% cellspacing=0 cellpadding=2 class=table style=\'font-size:smaller;\'>
                <tr>
                    <th style=\'text-align:left;\'>Mafioso</th><th style=\'text-align:left;\'>Title</th>
    ';

    if ($familyData['famID'] > 1) {
        print '
            <th style=\'text-align:center;\'>Level</th>
            <th style=\'text-align:center;\'>Status</th>
        ';
    }

    print '</tr>';

    $query = $db->query("SELECT userid, gangtitle, level FROM users WHERE gang = {$familyData['famID']} ORDER BY gangrank, level DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        if ($familyData['famID'] == 1) {
            print '
                <tr>
                    <td>' . mafiosoLight($row['userid']) . '</td>
                    <td>' . $row['gangtitle'] . '</td>
                </tr>
            ';
        } else {
            print '
                <tr>
                    <td>' . mafiosoLight($row['userid']) . '</td>
                    <td>' . $row['gangtitle'] . '</td>
                    <td style=\'text-align:center;\'>' . $row['level'] . '</td>
                    <td style=\'text-align:center;\'>' . status($row['userid']) . '</td>
                </tr>
            ';
        }
    }

    print '
            </table><br>
            <img src=\'/images/family/' . $familyData['famCoatOfArms'] . '\' width=300 height=300>
        </div>
        <p>Don ' . mafiosoLight($familyData['famDon']) . ' leads this ' . locationName($familyData['famHeadquarters']) . ' based Family with ' . number_format($familyData['famRespect']) . ' Respect.</p>
        <p>' . mysql_tex_out($familyData['famDesc']) . '</p>
    ';

    $qinv = $db->query("SELECT inv_id, inv_userid, inv_itemid FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 96");
    $rinv = mysqli_fetch_assoc($qinv);
    if ($rinv && $familyData['famID'] > 1) {
        print '
            <form class=center action=\'items.php?action=use2\' method=POST>
                <input type=hidden name=famid value=\'' . $familyData['famID'] . '\'>
                <input type=hidden name=invid value=\'' . $rinv['inv_id'] . '\'>
                <input type=hidden name=quant value=1>
                <input type=hidden name=useid value=\'' . $userId . '\'>
                <input type=submit value=\'Send in the Thief\'>
            </form>
        ';
    }

    $qnv2 = $db->query("SELECT inv_id, inv_userid, inv_itemid FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 112");
    $rnv2 = mysqli_fetch_assoc($qnv2);
    if ($rnv2 && $familyData['famID'] > 1) {
        print '
            &nbsp;&nbsp; <form class=center action=\'items.php?action=use2\' method=POST>
                <input type=hidden name=famid value=\'' . $familyData['famID'] . '\'>
                <input type=hidden name=invid value=\'' . $rnv2['inv_id'] . '\'>
                <input type=hidden name=quant value=\'1\'>
                <input type=hidden name=useid value=\'' . $userId . '\'>
                <input type=submit value=\'Send in the Midnight Bomber\'>
            </form>
        ';
    }
}

$application->header->endPage();
