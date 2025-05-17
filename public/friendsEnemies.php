<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$cid = isset($_GET['cid']) ? mysql_num($_GET['cid']) : 0;
$mid = isset($_GET['mid']) ? mysql_num($_GET['mid']) : 0;
$note = isset($_POST['note']) ? mysql_tex($_POST['note']) : '';
$type = isset($_POST['type']) ? mysql_tex($_POST['type']) : '';
$mafiosoID = isset($_POST['mafiosoID']) ? mysql_num($_POST['mafiosoID']) : 0;
$contactID = isset($_POST['contactID']) ? mysql_num($_POST['contactID']) : 0;

$row = mysqli_fetch_assoc($application->db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'friend' AND clContact = {$userId} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
$friends = $row['countValue'] ?? 0;
if ($friends < 0) {
    $friends = 0;
}

$row = mysqli_fetch_assoc($application->db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'enemy' AND clContact = {$userId} GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
$enemies = $row['countValue'] ?? 0;
if ($enemies < 0) {
    $enemies = 0;
}

if ($application->user['donatordays'] == 0) {
    print '
        <h3>Friends &amp; Enemies</h3>
        <p>The ability to vote for friends and enemies is restricted to donators.</p>
        <p><a href=\'donator.php\'>Donate to the game</a> or <a href=\'index.php\'>Head on home</a>.</p>
    ';

    $application->header->endPage();
    exit;
}

print '
    <h3>Friends &amp; Enemies</h3>
    <p>Select your friends and enemies carefully. The best in each field <em>will</em> receive benefits and penalties. This ability is available to Donators only, and if your Donator status lapses so will your list.</p>
    <p>You have been picked as a friend by ' . $friends . ' Mafioso and as an enemy by ' . $enemies . '.</p>
';

switch ($action) {
    case "adddo":
        add_do($application->db, $application->header, $userId, $mafiosoID, $note, $type);
        break;
    case "addenemy":
        add_enemy($mid);
        break;
    case "addfriend":
        add_friend($mid);
        break;
    case "changenote":
        change_note($application->db, $application->header, $userId, $cid);
        break;
    case "changenotedo":
        change_note_do($application->db, $application->header, $userId, $contactID, $note);
        break;
    case "remove":
        remove_contact($application->db, $application->header, $userId, $cid);
        break;
    case "view":
    default:
        view_lists($application->db, $userId);
        break;
}

function add_do(Database $db, Header $headers, int $userId, int $mafiosoID, string $note, string $type): void
{
    $qcl = $db->query("SELECT clID FROM contactList WHERE clSource = {$userId} AND clContact = {$mafiosoID}");
    if (mysqli_num_rows($qcl) || $userId == $mafiosoID || $mafiosoID == 0) {
        print '<p>You cannot add the same person twice nor can you add a ghost or even yourself. Now go away or I shall taunt you a second time.</p>';

        $headers->endpage();
        exit;
    }

    $db->query("INSERT INTO contactList(clSource, clContact, clType, clNote) VALUES({$userId}, {$mafiosoID}, '$type', '$note')");

    view_lists($db, $userId);
}

function add_enemy(int $mid): void
{
    print '
        <h5>Add Enemy</h5>
        <form action=\'friendsEnemies.php?action=adddo\' method=POST>
    ';

    if ($mid > 0) {
        print 'Add ' . mafioso($mid) . '<input type=hidden name=mafiosoID value=' . $mid . '>';
    } else {
        print 'Add ' . mafiosoMenu('mafiosoID', "AND rankCat='Player'");
    }

    print '
             as an Enemy with these comments: &nbsp;
             <input type=hidden name=type value=\'enemy\'>
             <input type=text name=note>
             <input type=submit value=\'Add Enemy\'>
         </form>
    ';
}

function add_friend(int $mid): void
{
    print '
        <h5>Add Friend</h5>
        <form action=\'friendsEnemies.php?action=adddo\' method=POST>
    ';

    if ($mid > 0) {
        print 'Add ' . mafioso($mid) . '<input type=hidden name=mafiosoID value=' . $mid . '>';
    } else {
        print 'Add ' . mafiosoMenu('mafiosoID', "AND rankCat='Player'");
    }

    print '
             as a Friend with these comments: &nbsp;
            <input type=hidden name=type value=\'friend\'>
            <input type=text name=note>
            <input type=submit value=\'Add Friend\'>
        </form>
    ';
}

function change_note(Database $db, Header $headers, int $userId, int $cid): void
{
    $query = $db->query("SELECT clID, clNote FROM contactList WHERE clID = {$cid} AND clSource = {$userId}");
    if (mysqli_num_rows($query) == 0) {
        print '
            <p>Stop trying to edit comments that aren\'t yours. Bad monkey, no banana.</p>
            <p><a href=\'home.php\'>Go home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    print '
         Comments:
        <form action=\'friendsEnemies.php?action=changenotedo\' method=POST>
            <input type=hidden name=contactID value=\'' . $cid . '\'>
            <textarea rows=1 cols=30 name=note>' . mysql_tex_edit($row['clNote']) . '</textarea>
            <br><input type=submit value=\'Change Note\'>
        </form>
    ';
}

function change_note_do(Database $db, Header $headers, int $userId, int $contactID, string $note): void
{
    $query = $db->query("SELECT clID FROM contactList WHERE clID = {$contactID} AND clSource = {$userId}");
    if (mysqli_num_rows($query) == 0) {
        print '
            <p>Stop trying to edit comments that aren\'t yours. Bad monkey, no banana.</p>
            <p><a href=\'home.php\'>Go home</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE contactList SET clNote = '{$note}' WHERE clID = {$contactID} AND clSource = {$userId}");

    view_lists($db, $userId);
}

function remove_contact(Database $db, Header $headers, int $userId, int $cid): void
{
    $query = $db->query("SELECT clID, clType FROM contactList WHERE clID = {$cid} AND clSource = {$userId}");
    $row = mysqli_fetch_assoc($query);
    if (mysqli_num_rows($query) == 0) {
        print '
            <p>They are not your ' . $row['clType'] . ', and so you cannot remove them.</p>
            <p><a href=\'friendsEnemies.php\'>Back to your contacts</a>.</p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("DELETE FROM contactList WHERE clID = {$cid} AND clSource = {$userId}");

    view_lists($db, $userId);
}

function view_lists($db, $userId): void
{
    print '
        <table width=95% cellpadding=1 cellspacing=0 class=table style=\'font-size:smaller;\'>
            <tr>
                <td colspan=8><strong>Friends &nbsp; </strong><em>(most liked: &nbsp;
    ';

    $qfc = $db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'friend' GROUP BY clContact ORDER BY countValue DESC LIMIT 5");
    $r = 0;
    while ($rfc = mysqli_fetch_assoc($qfc)) {
        $r++;
        if ($r > 1) {
            print '&nbsp;&middot;&nbsp;';
        }

        print mafiosoLight($rfc['clContact']);
    }

    print '
                )</em>
            </td>
            <td><a href=\'friendsEnemies.php?action=addfriend\'>add new friend</a></td>
        </tr>
        <tr>
            <th></th>
            <th class=center>Level</th>
            <th class=center>Rank</th>
            <th class=center>Cash</th>
            <th class=center>Token</th>
            <th class=center>Location</th>
            <th class=center>Status</th>
            <th>Note</th>
            <th class=center>Action</th>
        </tr>
    ';

    $query = $db->query("SELECT cl.clID, cl.clNote, u.userid, u.comRank, u.level, u.money, u.respect, u.location, u.jail, u.hospital FROM contactList cl LEFT JOIN users u ON cl.clContact = u.userid WHERE cl.clSource = {$userId} AND clType = 'friend' ORDER BY u.username");
    while ($row = mysqli_fetch_assoc($query)) {
        $hosp = '';
        if ($row['hospital'] > 1) {
            $hosp = '<span class=lighter>(H ' . $row['hospital'] . ')</span>';
        }

        $jail = '';
        if ($row['jail'] > 1) {
            $jail = '<span class=lighter>(J ' . $row['jail'] . ')</span>';
        }

        print '
            <tr>
                <td>' . mafiosoLight($row['userid']) . '</td>
                <td class=center>' . $row['level'] . '</td>
                <td class=center>' . $row['comRank'] . '</td>
                <td style=\'text-align:right;\'>' . moneyFormatter($row['money']) . '</td>
                <td class=center>' . $row['respect'] . '</td>
                <td class=center>' . locationName($row['location']) . ' ' . $hosp . ' ' . $jail . '</td>
                <td class=center>' . status($row['userid']) . '</td>
                <td>' . mysql_tex_out($row['clNote']) . '</td>
                <td class=center>
                    <a href=\'friendsEnemies.php?action=changenote&cid=' . $row['clID'] . '\'>note</a> &nbsp;&middot;&nbsp;
                    <a href=\'friendsEnemies.php?action=remove&cid=' . $row['clID'] . '\'>remove</a>
                </td>
            </tr>
        ';
    }

    print '
        <tr><td colspan=9><br><br></td></tr>
        <tr><td colspan=8><strong>Enemies &nbsp; </strong><em>(most hated: &nbsp;
    ';

    $qec = $db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'enemy' GROUP BY clContact ORDER BY countValue DESC LIMIT 5");
    $r = 0;
    while ($rec = mysqli_fetch_assoc($qec)) {
        $r++;
        if ($r > 1) {
            print '&nbsp;&middot;&nbsp;';
        }

        print mafiosoLight($rec['clContact']);
    }

    print '
                )</em>
            </td>
            <td><a href=\'friendsEnemies.php?action=addenemy\'>add new enemy</a></td>
        </tr>
        <tr>
            <th></th>
            <th class=center>Level</th>
            <th class=center>Rank</th>
            <th class=center>Cash</th>
            <th class=center>Token</th>
            <th class=center>Location</th>
            <th class=center>Status</th>
            <th>Note</th>
            <th class=center>Action</th>
        </tr>
    ';

    $query = $db->query("SELECT cl.clID, cl.clNote, u.userid, u.comRank, u.level, u.money, u.respect, u.location, u.jail, u.hospital FROM contactList cl LEFT JOIN users u ON cl.clContact = u.userid WHERE cl.clSource = {$userId} AND clType = 'enemy' ORDER BY u.username");
    while ($row = mysqli_fetch_assoc($query)) {
        $hosp = '';
        if ($row['hospital'] > 1) {
            $hosp = '<span class=lighter>(H ' . $row['hospital'] . ')</span>';
        }

        $jail = '';
        if ($row['jail'] > 1) {
            $jail = '<span class=lighter>(J ' . $row['jail'] . ')</span>';
        }

        $a = '';
        $rat = mysqli_fetch_assoc($db->query("SELECT userid FROM coursesdone WHERE userid = {$userId} AND courseid = 26"));
        if ($rat['userid'] == $userId) {
            $a = '<a href=\'attack.php?ID=' . $row['userid'] . '\'><img src=\'/images/donator.gif\' alt=Attack title=Attack></a>&nbsp;';
        }

        print '
            <tr>
                <td>' . $a . ' ' . mafiosoLight($row['userid']) . '</td>
                <td class=center>' . $row['level'] . '</td>
                <td class=center>' . $row['comRank'] . '</td>
                <td style=\'text-align:right;\'>' . moneyFormatter($row['money']) . '</td>
                <td class=center>' . $row['respect'] . '</td>
                <td class=center>' . locationName($row['location']) . ' ' . $hosp . ' ' . $jail . '</td>
                <td class=center>' . status($row['userid']) . '</td>
                <td>' . mysql_tex_out($row['clNote']) . '</td>
                <td class=center>
                    <a href=\'friendsEnemies.php?action=changenote&cid=' . $row['clID'] . '\'>note</a> &nbsp;&middot;&nbsp;
                    <a href=\'friendsEnemies.php?action=remove&cid=' . $row['clID'] . '\'>remove</a>
                </td>
            </tr>
        ';
    }

    print '</table>';
}

$application->header->endPage();
