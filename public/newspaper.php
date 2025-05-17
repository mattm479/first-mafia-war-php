<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$postID = isset($_GET['postID']) ? mysql_num($_GET['postID']) : 0;
$article = isset($_POST['article']) ? mysql_tex($_POST['article']) : '';
$blue = isset($_POST['blue']) ? mysql_tex($_POST['blue']) : '';
$comic = isset($_POST['comic']) ? mysql_tex($_POST['comic']) : '';

if ($application->user['gagOrder']) {
    print '
        <h3 style=\'font-color:red;\'>Gag Order in force</h3>
        <p>You have been banned from communicating with others for ' . $application->user['gagOrder'] . ' more hours.</p>
        <p>The main reason was ' . $application->user['gagReason'] . '. I\'m sure there were others as well that went undocumented. Try and be more polite please.</p>
    ';

    $application->header->endPage();
    exit;
}

$inv = mysqli_fetch_assoc($application->db->query("SELECT inv_itemid FROM inventory WHERE inv_userid = {$userId} AND inv_itemid = 636"));
$fee = ($application->user['level'] * 15);
$ftxt = 'Just ' . moneyFormatter($fee) . ' to post.';
if ($inv['inv_itemid'] == 636 || $application->user['jail'] || $application->user['hospital']) {
    $ftxt = 'Currently free to post.';
    $fee = 0;
}

print '
    <h3>The Mafia Messenger</h3>
    <p>Welcome to your local paper. The articles come and go, but disrespect lasts forever. ' . $ftxt . '<br>
';

$application->db->query("UPDATE users SET newNews = 0 WHERE userid = {$userId}");

switch ($action) {
    case "bluepost":
        bluepost($application->db, $application->user, $postID);
        break;
    case "blueroom":
        blueroom($application->db, $application->user, $userId);
        break;
    case "delete":
        delete($application->db, $application->user, $postID);
        break;
    case "post":
        post($application->db, $application->header, $application->user, $userId, $article, $comic, $blue, $fee);
        break;
    case "read":
    default:
        read($application->db, $application->user, $userId);
        break;
}

function bluepost(Database $db, array $user, int $postID): void
{
    if ($user['rankCat'] == 'Staff' || $user['rank'] == 'Don') {
        $rnew = mysqli_fetch_assoc($db->query("SELECT newsBlueRoom FROM news WHERE newsID = {$postID}"));
        if ($rnew['newsBlueRoom'] == 0) {
            $db->query("UPDATE news SET newsBlueRoom = 1 WHERE newsID = {$postID}");
            staffLogAdd("Put {$postID} into the blue room.");

            print "
                <br><br><p>Your post has been put in the blue room.</p>
                <p>Go back to the <a href='newspaper.php'>Newspaper</a></p>
            ";
        } else {
            $db->query("UPDATE news SET newsBlueRoom = 0 WHERE newsID = {$postID}");
            staffLogAdd("Removed {$postID} from the blue room.");

            print "
                <br><br><p>Your post has been removed from the blue room.</p>
                <p>Go back to the <a href='newspaper.php'>Newspaper</a></p>
            ";
        }
    }
}

function blueroom(Database $db, array $user, int $userId): void
{
    if ($user['newsBlueRoom'] == 0) {
        $db->query("UPDATE users SET newsBlueRoom = 1 WHERE userid = {$userId}");
    } else {
        $db->query("UPDATE users SET newsBlueRoom = 0 WHERE userid = {$userId}");
    }

    header("Location:newspaper.php");
}

function delete(Database $db, array $user, int $postID)
{
    if ($user['rankCat'] == 'Staff' || $user['rank'] == 'Don') {
        $db->query("UPDATE news SET newsDelete = 1 WHERE newsID = {$postID}");
        staffLogAdd("Deleted news article ID: {$postID}");
    }

    header("Location:newspaper.php");
}

function read(Database $db, array $user, int $userId): void
{
    if ($user['newsBlueRoom'] == 0) {
        $blueroom = '<a href=\'newspaper.php?action=blueroom\'>Enter Blue Room</a>';
    } else {
        $blueroom = '<a href=\'newspaper.php?action=blueroom\'>Leave Blue Room</a>';
    }

    print $blueroom . ' &nbsp;&middot;&nbsp; <a href=\'newspaper.php?action=post\'><strong>Post article</strong></a></p>';

    $blue2 = '';
    if ($user['newsBlueRoom'] == 0) {
        $blue2 = 'AND newsBlueRoom = 0';
    }

    print '
        <table width=95% cellpadding=2 cellspacing=0 class=table>
            <tr>
                <td valign=top>
                    <table width=100% cellpadding=2 cellspacing=0 class=table>
    ';

    $query = $db->query("SELECT newsID, newsFrom, newsText, newsTime FROM news WHERE newsImage = '0' AND newsDelete = 0 {$blue2} ORDER BY newsTime DESC LIMIT 40");
    while ($row = mysqli_fetch_assoc($query)) {
        $qene = $db->query("SELECT clID FROM contactList WHERE clSource = {$userId} AND clContact = {$row['newsFrom']} AND clType = 'enemy'");
        $tdclass = 'mostborders';
        if (mysqli_fetch_assoc($qene)) {
            $tdclass = 'mostbordersblank';
        }

        print '
            <tr><td class=' . $tdclass . '>' . mysql_tex_out($row['newsText']) . '</td></tr>
            <tr>
                <td class=fewborders>
                    <div style=\'font-size:smaller\'>' . mafioso($row['newsFrom']) . ' - 
                        <span class=light>' . date('F j Y', $row['newsTime']) . ' at ' . date('g:i a', $row['newsTime']) . '</span> &nbsp;
        ';

        if ($user['rankCat'] == 'Staff' || $user['rank'] == 'Don') {
            print '
                <a title=delete href=\'newspaper.php?action=delete&postID=' . $row['newsID'] . '\'><span class=staffview>[del]</span></a> &nbsp; 
                <a title=\'blue shift\' href=\'newspaper.php?action=bluepost&postID=' . $row['newsID'] . '\'><span class=staffview>[blue]</span></a> &nbsp; 
            ';
        }

        print '
                    </div>
                </td>
            </tr>
            <tr><td style=\'font-size:1px;\'>&nbsp;</td></tr>
        ';
    }

    print '
            </table>
        </td>
        <td width=210 valign=top>
            <table width=100% cellpadding=2 cellspacing=0 class=table>
    ';

    $q2 = $db->query("SELECT newsID, newsFrom, newsTime FROM news WHERE newsText = '0' AND newsDelete = 0 {$blue2} ORDER BY newsTime DESC LIMIT 9");
    while ($r2 = mysqli_fetch_assoc($q2)) {
        $qene = $db->query("SELECT clID FROM contactList WHERE clSource={$userId} AND clContact={$r2['newsFrom']} AND clType='enemy'");
        $info2 = $r2['newsImage'];
        if (mysqli_fetch_assoc($qene)) {
            $info2 = '/images/mafioso/thestupiditburns.jpg';
        }

        print '
            <tr><td class=mostborders><img src=\'' . $info2 . '\' width=200 height=200 alt=\'Image Failed\' title=\'' . date('F j Y', $r2['newsTime']) . ' at ' . date('g:i a', $r2['newsTime']) . '\'></td></tr>
            <tr><td class=fewborders>' . mafiosoLight($r2['newsFrom']) . '&nbsp; 
        ';

        if ($user['rankCat'] == 'Staff' || $user['rank'] == 'Don') {
            print '
                <a title=delete href=\'newspaper.php?action=delete&postID=' . $r2['newsID'] . '\'><span class=staffview>[del]</span></a> &nbsp; 
                <a title=\'blue shift\' href=\'newspaper.php?action=bluepost&postID=' . $r2['newsID'] . '\'><span class=staffview>[blue]</span></a> &nbsp; 
            ';
        }

        print '
                </td>
            </tr>
            tr><td>&nbsp;</td></tr>
        ';
    }

    print '
                    </table>
                </td>
            </tr>
        </table>
    ';
}

function post(Database $db, Header $headers, array $user, int $userId, string $article, string $comic, string $blue, int $fee): void
{
    if (isset($_POST['article']) || isset($_POST['comic'])) {
        if ($user['money'] < $fee) {
            print '
                <p>You don\'t have enough money to post an article.</p>
                <p><a href=\'home.php\'>Home</a></p>
            ';

            $headers->endpage();
            exit;
        }

        $coffee = $user['newsCoffee'];
        newsPost($userId, $article, $comic, $blue);
        $db->query("UPDATE users SET money = money - {$fee}, newsCoffee = 1 WHERE userid = {$userId}");

        if ($coffee == 0) {
            itemAdd(56, 0, $userId, 0, 1);

            print '
                <p>For your first post of the day, you earn a free cup of coffee from the cafe. Enjoy.</p>
                <p><a href=\'newspaper.php\'>Read the news</a>
            ';

            $headers->endpage();
            exit;
        }

        header("Location:newspaper.php");
    } else {
        print '
            <hr><br>
            You may post an article in the social column. You may use &lt;strong&gt;<strong>bold text</strong>&lt;/strong&gt; and &lt;em&gt;<em>italic text</em>&lt;/em&gt; wrappers.<br>
            <form action=\'newspaper.php?action=post\' method=POST>
                <input type=hidden name=comic value=\'0\'><br>
                An article is considered <em>Blue</em> if you wouldn\'t tell your mother. OK, my mother. <input type=checkbox name=blue value=\'1\'>Make Blue</input>
                <textarea rows=5 cols=75 name=article></textarea><br>
                <input type=submit value=\'Add Article\'>
            </form>
            <br><hr><br>
            ... or you post an image in the comics.<br>
            <form action=\'newspaper.php?action=post\' method=POST>
                <input type=hidden name=article value=\'0\'><br>
                An image is considered <em>Blue</em> if you wouldn\'t show your mother. OK, my mother. <input type=checkbox name=blue value=\'1\'>Make Blue</input>
                <input type=text name=comic size=60><br>
                <input type=submit value=\'Add Comic\'>
            </form>
            <blockquote>
                <p>
                    Please note that your image must be externally hosted, <a href=\'http://imageshack.us\'>ImageShack</a> does a pretty decent job of it. Remember you ONLY want to use the actual image link, not all the other stuff.  It should look something like this:<br>
                    &nbsp; &nbsp; &#104;&#x74;&#116;&#112;&#x3a;&#x2f;&#x2f;&#119;&#x77;&#x77;&#x2e;&#102;&#x69;&#114;&#115;&#x74;&#x6d;&#x61;&#x66;&#105;&#97;&#x77;&#97;&#x72;&#46;&#99;&#111;&#109;&#x2f;&#105;&#109;&#x61;&#x67;&#101;&#115;&#47;&#x6d;&#x61;&#x66;&#105;&#111;&#115;&#x6f;&#47;&#x4b;&#101;&#x66;&#x65;&#46;&#x6a;&#112;&#103;
                </p>
                <p>Any images that are not 200x200 will be automatically resized so if you want it to look good, please provide the right size.</p>
            </blockquote>
        ';
    }
}

$application->header->endPage();
