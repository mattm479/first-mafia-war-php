<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$act = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$fid = isset($_GET['fid']) ? mysql_tex($_GET['fid']) : '';
$eid = isset($_GET['eid']) ? mysql_tex($_GET['eid']) : '';
$stt = isset($_GET['stt']) ? mysql_num($_GET['stt']) : 0;
$tx1 = isset($_POST['tx1']) ? mysql_tex($_POST['tx1']) : '';
$tx2 = isset($_POST['tx2']) ? mysql_tex($_POST['tx2']) : '';
$tx3 = isset($_POST['tx3']) ? mysql_tex($_POST['tx3']) : '';

print '<h3>First Mafia War Forums</h3>';

if ($user['gagOrder']) {
    print '
        <h3 style=\'font-color:red;\'>Gag Order in force</h3>
        <p>You have been banned from communicating with others for ' . $user['gagOrder'] . ' more hours.</p>
        <p>The main reason was ' . $user['gagReason'] . '. I\'m sure there were others as well that went undocumented. Try and be more polite please.</p>
    ';
    
    $headers->endpage();
    exit;
}

switch ($act) {
    case 'vforum':
        view_forum($db, $headers, $user, $userId, $fid);
        break;
    case 'vtopic':
        view_topic($db, $headers, $user, $userId, $eid, $fid, $stt);
        break;
    case 'ctopic':
        create_topic($db, $headers, $user, $userId, $fid, $tx1, $tx2, $tx3);
        break;
    case 'cposts':
        create_post($db, $headers, $user, $userId, $fid, $tx1);
        break;
    case 'eposts':
        edit_post($db, $headers, $user, $userId, $fid, $tx1);
        break;
    case 'dforum':
        delete_forum($db, $fid);
        break;
    case 'dtopic':
        delete_topic();
        break;
    case 'dposts':
        delete_post();
        break;
    default:
        index($db, $user);
        break;
}

function index(Database $db, array $user): void
{
    print '
        <br><table width=95% border=0 cellpadding=3 cellspacing=0 class=table>
            <tr>
                <th style=\'text-align:left;\'>Forum</th>
                <th>Topics</th>
                <th>Posts</th>
                <th style=\'text-align:left;\'>Last Post</th>
            </tr>
    ';

    $query = $db->query("SELECT foID, foTitle, foSubtitle FROM forum WHERE foDeleted = 0 AND ((foView = 0 OR foView = {$user['gang']}) OR ('{$user['rank']}' = 'Don' AND foID = 10) OR '{$user['rankCat']}' = 'Staff') ORDER BY foID");
    while ($row = mysqli_fetch_assoc($query)) {
        $topicount = mysqli_num_rows($db->query("SELECT ftID FROM forumTopics WHERE ftForumID = {$row['foID']} AND ftDeleted = 0"));
        $postcount = mysqli_num_rows($db->query("SELECT fpID FROM forumPosts WHERE fpForumID = {$row['foID']} AND fpDeleted = 0"));
        $rp = mysqli_fetch_assoc($db->query("SELECT ft.ftTitle, fp.fpTopicID, fp.fpMafioso, fp.fpTime FROM forumPosts fp LEFT JOIN forumTopics ft ON ft.ftID = fp.fpTopicID WHERE fp.fpForumID = {$row['foID']} AND fp.fpDeleted = 0 ORDER BY fp.fpTime DESC LIMIT 1"));

        print '
            <tr>
                <td><a href=\'forum.php?act=vforum&fid=' . $row['foID'] . '\'><strong>' . $row['foTitle'] . '</strong></a><br><span class=light>' . $row['foSubtitle'] . '</span></td>
                <td class=center>' . $topicount . '</td>
                <td class=center>' . $postcount . '</td>
            ';
        if (isset($rp['fpMafioso']) && $rp['fpMafioso'] > 0) {
            print '<td><a href=\'forum.php?act=vtopic&fid=' . $rp['fpTopicID'] . '\'>' . $rp['ftTitle'] . '</a> by ' . mafiosoLight($rp['fpMafioso']) . '<br><span class=light>' . date('F j Y, g:i a', $rp['fpTime']) . '</span></td></tr>';
        } else {
            print '<td><a href=\'forum.php?act=ctopic&fid=' . $row['foID'] . '\'>Create the first topic</a><br><span class=light>Do it!  Do it now!  Speak your mind!</span></td></tr>';
        }
        if ($row['foID'] == 5 or $row['foID'] == 11) {
            print '<tr><td colspan=4><hr></td></tr>';
        }
    }

    print '</table>';
}

function view_forum(Database $db, Header $headers, array $user, int $userId, int $fid, int $sid = 0): void
{
    if ($sid > 0) {
        $fid = $sid;
    }

    $rf = mysqli_fetch_assoc($db->query("SELECT foView, foTitle FROM forum WHERE foID = {$fid}"));
    if ($rf['foView'] != 0 && $rf['foView'] != $user['gang'] && $user['rankCat'] != 'Staff' && ($user['rank'] != 'Don' && $rf['foView'] == 10)) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    print '
        <br><table width=95% cellpadding=3 cellspacing=0 class=table>
            <tr>
                <td colspan=4>
                    <div class=floatright>&middot; <a href=\'forum.php?act=ctopic&fid=' . $fid . '\'>new topic</a> &middot;</div>
                    <a href=\'forum.php\'>Home</a> &nbsp;&raquo;&nbsp;
                    <a href=\'forum.php?act=vforum&fid=' . $fid . '\'>' . $rf['foTitle'] . '</a>
                </td>
            </tr>
            <tr>
                <th style=\'text-align:left;\'>Topics</th>
                <th>Posts</th>
                <th>First Post</th>
                <th>Last Post</th>
            </tr>
    ';

    $qt = $db->query("SELECT ftID, ftTitle, ftSubtitle FROM forumTopics WHERE ftForumID = {$fid} AND ftDeleted = 0 ORDER BY ftTime DESC");
    while ($rt = mysqli_fetch_assoc($qt)) {
        $postcount = mysqli_num_rows($db->query("SELECT fpID FROM forumPosts WHERE fpTopicID = {$rt['ftID']} AND fpDeleted = 0"));
        $rfp = mysqli_fetch_assoc($db->query("SELECT fpMafioso, fpTime FROM forumPosts WHERE fpTopicID = {$rt['ftID']} AND fpDeleted = 0 ORDER BY fpTime LIMIT 1"));
        $rlp = mysqli_fetch_assoc($db->query("SELECT fpMafioso, fpTime FROM forumPosts WHERE fpTopicID = {$rt['ftID']} AND fpDeleted = 0 ORDER BY fpTime DESC LIMIT 1"));

        print '
            <tr>
                <td><a href=\'forum.php?act=vtopic&fid=' . $rt['ftID'] . '\'><strong>' . $rt['ftTitle'] . '</strong></a><br><span class=light>' . $rt['ftSubtitle'] . '</span></td>
                <td class=center>' . $postcount . '</td>
                <td class=center>' . mafiosoLight($rfp['fpMafioso']) . '<br><span class=light>' . date('F j Y, g:i a', $rfp['fpTime']) . '</span></td>
                <td class=center>' . mafiosoLight($rlp['fpMafioso']) . '<br><span class=light>' . date('F j Y, g:i a', $rlp['fpTime']) . '</span></td>
            </tr>
        ';
    }

    print '</table>';

    $db->query("UPDATE users SET newForum = 0 WHERE userid = {$userId}");
}

function view_topic(Database $db, Header $headers, array $user, int $userId, string $eid, string $fid, int $stt, int $sid = 0): void
{
    if ($sid > 0) {
        $fid = $sid;
    }

    $rt = mysqli_fetch_assoc($db->query("SELECT ftID, ftForumID, ftTitle FROM forumTopics WHERE ftID = {$fid}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT foID, foView, foTitle FROM forum WHERE foID = {$rt['ftForumID']}"));

    if ($eid) {
        $rep = mysqli_fetch_assoc($db->query("SELECT * FROM forumPosts WHERE fpID = {$eid}"));
    }

    if ($rf['foView'] != 0 && $rf['foView'] != $user['gang'] && $user['rankCat'] != 'Staff' && ($user['rank'] != 'Don' && $rf['foView'] == 10)) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $postcount = mysqli_num_rows($db->query("SELECT fpID FROM forumPosts WHERE fpTopicID = {$fid} AND fpDeleted = 0"));
    $pages = ceil($postcount / 25);

    print '
        <br><table width=95% cellpadding=3 cellspacing=0 class=table>
            <tr>
                <td colspan=2>
                    <div class=floatright style=\'text-align:right;\'>
    ';

    if ($user['rankCat'] == 'Staff' || ($rf['foView'] == $user['gang'] && $user['rank'] == 'Don')) {
        print '&middot; <a href=\'forum.php?act=dtopic&fid=' . $rt['ftID'] . '\'>delete topic</a> &middot; ';
    }

    print ' <em>page&nbsp;';

    for ($i = 1; $i <= $pages; $i++) {
        $pst = ($i - 1) * 25;

        print '<a href=\'forum.php?act=vtopic&fid=' . $fid . '&stt=' . $pst . '\'>';

        if ($pst == $stt) {
            print '<strong>';
        }

        print $i;

        if ($pst == $stt) {
            print '</strong>';
        }

        print '</a>&nbsp;';

        if ($i % 25 == 0) {
            print '<br>';
        }
    }
    
    print '</em></div><a href=\'forum.php\'>Home</a> &nbsp;&raquo;&nbsp;<a href=\'forum.php?act=vforum&fid=' . $rf['foID'] . '\'>' . $rf['foTitle'] . '</a> &nbsp;&raquo;&nbsp;<a href=\'forum.php?act=vtopic&fid=' . $fid . '\'>' . $rt['ftTitle'] . '</a></td></tr>';

    $qp = $db->query("SELECT fpMafioso FROM forumPosts WHERE fpTopicID = {$fid} AND fpDeleted = 0 ORDER BY fpTime LIMIT {$stt}, 30");
    while ($rp = mysqli_fetch_assoc($qp)) {
        $mr = mysqli_fetch_assoc($db->query("SELECT mugForum, mugForumSig FROM users WHERE userid = {$rp['fpMafioso']}"));

        print '
            <tr>
                <th colspan=2 style=\'text-align:left;font-size:smaller;font-weight:normal;\'>
                    <div class=floatright><em>' . date('F j Y, g:ia', $rp['fpTime']) . '</em></div>
                     &nbsp;<span>' . mafioso($rp['fpMafioso']) . '</span>
                 </th>
             </tr>
             <tr><td valign=top class=center>
                <img src=\'' . $mr['mugForum'] . '\' width=100 height=100><br><br>
        ';

        if ($user['rankCat'] == 'Staff' || $userId == $rp['fpMafioso'] || ($user['gang'] == $rf['foView'] && $user['gangrank'] == 1)) {
            print '<a href=\'forum.php?act=vtopic&fid=' . $rp['fpTopicID'] . '&eid=' . $rp['fpID'] . '\'>Edit</a>&nbsp;&middot;&nbsp;<a href=\'forum.php?act=dposts&fid=' . $rp['fpID'] . '\'>Delete</a>&nbsp;<br>';
        }

        if ($eid == $rp['fpID']) {
            print '</td><td class=hilite valign=top>' . mysql_tex_out($rp['fpText']) . '<br><br><em>' . mysql_tex_out($mr['mugForumSig']) . '</em></td></tr>';
        } else {
            print '</td><td valign=top>' . mysql_tex_out($rp['fpText']) . '<br><br><em>' . mysql_tex_out($mr['mugForumSig']) . '</em></td></tr>';
        }
    }

    if ($eid) {
        print '
                    <tr><td class=center colspan=2><br><hr><br><strong>You are Editing Your Earlier Post</strong><br></td></tr>
                    <tr>
                        <td class=center>Post:</td>
                        <td>
                            <form action=\'forum.php?act=eposts&fid=' . $eid . '\' method=POST>
                                <textarea rows=10 cols=75 name=tx1>' . mysql_tex_edit($rep['fpText']) . '</textarea><br>
                                <input type=submit value=\'Edit Post\'>
                            </form>
                        </td>
                    </tr>
            </table>
        ';
    } else {
        print '
                <tr><td class=center colspan=2><br><hr><br><strong>Add to the conversation</strong><br></td></tr>
                <tr>
                    <td class=center>Post:</td>
                    <td>
                        <form action=\'forum.php?act=cposts&fid=' . $rt['ftID'] . '\' method=POST>
                            <textarea rows=10 cols=75 name=tx1></textarea><br>
                            <input type=submit value=\'Post Reply\'>
                        </form>
                    </td>
                </tr>
            </table>
        ';
    }

    $db->query("UPDATE users SET newForum = 0 WHERE userid = {$userId}");
}

function create_topic(Database $db, Header $headers, array $user, int $userId, string $fid, string $tx1, string $tx2, string $tx3): void
{
    $rf = mysqli_fetch_assoc($db->query("SELECT foView, foTitle FROM forum WHERE foID = {$fid}"));
    if ($user['rankCat'] != 'Staff' && $rf['foView'] != 0 && $rf['foView'] != $user['gang'] && ($user['rank'] != 'Don' && $rf['foView'] == 10)) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page let alone add a topic here. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($tx3) {
        if (!$tx1) {
            $tx1 = 'No title provided';
        }

        $db->query("INSERT INTO forumTopics(ftForumID, ftMafioso, ftTitle, ftSubtitle, ftViews, ftTime, ftLocked, ftPinned, ftDeleted) VALUES ({$fid}, {$userId}, '{$tx1}', '{$tx2}', 1, unix_timestamp(), 0, 0, 0)");
        $i = mysqli_insert_id($db);

        $db->query("INSERT INTO forumPosts(fpForumID, fpTopicID, fpMafioso, fpText, fpTime, fpDeleted) VALUES ({$fid}, {$i}, {$userId}, '{$tx3}', unix_timestamp(), 0)");
        $db->query("UPDATE users SET newForum = newForum + 1 WHERE userid != {$userId} AND ({$rf['foView']} = 0 OR gang = {$rf['foView']})");

        $headers->endpage();
        exit;
    }

    print '
        <form action=\'forum.php?act=ctopic&fid=' . $fid . '\' method=POST>
            <br><table width=95% cellpadding=3 cellspacing=0 class=table>
                <tr><td colspan=4><p>You are creating a new topic in the <a href=\'forum.php?act=vforum&fid=' . $fid . '\'>' . $rf['foTitle'] . '</a> Forum.</p></td></tr>
                <tr>
                    <td align=right>Title:</td>
                    <td><input type=text name=tx1 size=30 value=\'\'></td>
                    <td align=right>Subtitle:</td><td align=left><input type=text name=tx2 size=30 value=\'\'></td>
                </tr>
                <tr><td colspan=4 class=center>&middot; ---- &middot;</td></tr>
                <tr>
                    <td align=right>First Post:<br></td>
                    <td colspan=3><textarea rows=10 cols=75 name=tx3></textarea></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan=3><input type=submit value=\'Create New Topic\'></td>
                </tr>
            </table>
        </form>
    ';
}

function create_post(Database $db, Header $headers, array $user, int $userId, string $fid, string $tx1): void
{
    $rt = mysqli_fetch_assoc($db->query("SELECT ftForumID FROM forumTopics WHERE ftID = {$fid}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT foID, foView, foTitle FROM forum WHERE foID = {$rt['ftForumID']}"));
    if ($user['rankCat'] != 'Staff' && $rf['foView'] != 0 && $rf['foView'] != $user['gang'] && ($user['rank'] != 'Don' && $rf['foView'] == 10)) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page let alone add a post here. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($tx1) {
        $db->query("INSERT INTO forumPosts (fpForumID, fpTopicID, fpMafioso, fpText, fpTime, fpDeleted) VALUES({$rf['foID']}, {$fid}, {$userId}, '{$tx1}', unix_timestamp(), 0)");
        $db->query("UPDATE forumTopics SET ftTime = unix_timestamp() WHERE ftID = {$fid}");
    }

    $db->query("UPDATE users SET newForum = newForum + 1 WHERE userid != {$userId} AND ({$rf['foView']} = 0 OR gang = {$rf['foView']})");

    if ($rf['foView'] == 10) {
        $db->query("UPDATE users SET newForum = newForum + 1 WHERE userid != {$userId} AND `rank` IN ('Capo', 'Don')");
    }
}

function edit_post(Database $db, Header $headers, array $user, int $userId, string $fid, string $tx1): void
{
    $rp = mysqli_fetch_assoc($db->query("SELECT fpTopicID FROM forumPosts WHERE fpID = {$fid}"));
    $rt = mysqli_fetch_assoc($db->query("SELECT ftForumID FROM forumTopics WHERE ftID = {$rp['fpTopicID']}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT foID, foView, foTitle FROM forum WHERE foID = {$rt['ftForumID']}"));
    if ($rf['foView'] != 0 && $rf['foView'] != $user['gang'] && $user['rankCat'] != 'Staff') {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page let alone add a post here. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($tx1) {
        $tx1 .= '<br><br> <em>~ Edited by ' . mafiosoName($userId) . ' ' . date('F j Y, g:i a', time()) . '</em>';
        $db->query("UPDATE forumPosts SET fpText = '{$tx1}' WHERE fpID = {$fid}");
    }
}

function delete_forum(Database $db, string $fid): void
{
    $db->query("UPDATE forum SET foDeleted = 1 WHERE foID = {$fid}");
    $db->query("UPDATE forumTopics SET ftDeleted = 1 WHERE ftForumID = {$fid}");
    $db->query("UPDATE forumPosts SET fpDeleted = 1 WHERE fpForumID = {$fid}");
}

function delete_topic(Database $db, Header $headers, array $user, string $fid): void
{
    $rt = mysqli_fetch_assoc($db->query("SELECT ftForumID FROM forumTopics WHERE ftID = {$fid}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT foID, foView, foTitle FROM forum WHERE foID = {$rt['ftForumID']}"));
    if ($user['rankCat'] != 'Staff' && ($rf['foView'] != $user['gang'] && $user['rank'] != 'Don')) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page let alone delete a topic here. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE forumTopics SET ftDeleted = 1 WHERE ftID = {$fid}");
    $db->query("UPDATE forumPosts SET fpDeleted = 1 WHERE fpTopicID = {$fid}");
}

function delete_post(Database $db, Header $headers, array $user, int $userId, string $fid): void
{
    $rp = mysqli_fetch_assoc($db->query("SELECT fpForumID, fpTopicID FROM forumPosts WHERE fpID = {$fid}"));
    $rf = mysqli_fetch_assoc($db->query("SELECT foID, foView, foTitle FROM forum WHERE foID = {$rp['fpForumID']}"));
    $postcount = mysqli_num_rows($db->query("SELECT fpID FROM forumPosts WHERE fpTopicID = {$rp['fpTopicID']} AND fpDeleted = 0"));
    if ($userId != $rp['fpMafioso'] && $user['rankCat'] != 'Staff' && ($rf['foView'] != $user['gang'] && $user['gangrank'] != 1)) {
        print '
            <br><p>You are not permitted to view the super-secret contents of this page let alone delete a post here. Someone might sink a ship.</p>
            <p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    if ($postcount == 1) {
        print '
            <br><p>You cannot remove the last post in a topic - you must remove the topic and it will remove the last post.</p>
            <p>If you do not have the ability to do that, request assistance from staff.</p><p><a href=\'forum.php\'>Return to the main page</a></p>
        ';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE forumPosts SET fpDeleted = 1 WHERE fpID = {$fid}");
}

print '
    <br><br>
    <div align=center><img src=\'assets/images/photos/forums.jpg\' width=400 height=210 alt=Connections></div>
';

$headers->endpage();
