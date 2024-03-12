<?php

use Fmw\Database;
use Fmw\Header;

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action = isset($_POST['actn']) ? mysql_tex($_POST['actn']) : '';
$hide = isset($_POST['hyde']) ? mysql_tex($_POST['hyde']) : '';

print '
    <h3>Hide and Seek</h3>
    <div class=floatrightbox style=\'margin-left:5px;\'>
        <strong>Current Mafioso in Hiding</strong><br>
';

$qhide = $db->query("SELECT userid, hideLocation FROM users WHERE hideLocation != '0' ORDER BY username");
while ($rhide = mysqli_fetch_assoc($qhide)) {
    print ' &nbsp; ' . mafiosoLight($rhide['userid']);
    if ($userId == 1) {
        print ' <span class=light>&nbsp;&middot;&nbsp; ' . $rhide['hideLocation'] . '</span>';
    }

    print '<br>';
}

print '
    </div>
    <div style=\'float: left; padding-right: 8px;\'>
        <img src=\'assets/images/photos/hideandseek.jpg\' width=200 height=257 alt=\'Hide and Seek\'><br>
    </div>
';

switch ($action) {
    case 'hide':
        hide($db, $userId, $hide);
        break;
    case 'play':
        play($db, $headers, $user, $userId);
        break;
    case 'seek':
        seek($db, $headers, $user, $userId, $hide);
        break;
    default:
        index($user);
        break;
}

function index(array $user): void
{
    print '
        <p>It\'s easy. Just put up 2 Tokens of Respect and go hide. Then seek out other hidden players in various places. If you find someone, you get their 2 Tokens and they must start over.</p>
        <p>It costs just $550 to search a page for someone hidden there. The best way to find a person is to sneak up on them. So the faster you search, the harder it becomes to find someone. If you are patient, and search over time, your fee will remain the same, but if you search too quickly the fee will increase, though it does slowly come down over time.</p>
        <p>Different cities do not matter when hiding in common areas, nor does hiding in different stores. You are hiding in the location <em>immediately after</em> the URL. So if you are hiding in a store, you are hiding in the <em>business</em> area. Eventually we may open it up, but that\'s it for now.</p>
    ';

    if ($user['hideLocation'] == '0') {
        print '
            <p>So what are you waiting for?</p>
            <form action=\'gameHideSeek.php\' method=POST>
                <input type=hidden name=actn value=\'play\'>
                <input type=submit value=\'Get out there and Play\'>
            </form>
        ';
    } elseif ($user['hideLocation'] == '1') {
        print '<p>So what are you waiting for? Get out there and hide! You cannot seek until you hide. Yes, I know that does not make much sense, but that is the way life goes.</p>';
    } else {
        print '<p>Good, you have hidden well. You are hiding in the <strong><a href=\'' . $user['hideLocation'] . '.php\'>' . $user['hideLocation'] . '</a></strong> area of the game.</p>';
    }
}

function play(Database $db, Header $headers, array $user, int $userId): void
{
    if ($user['respect'] < 3) {
        print '<p>You do not have enough Respect to play in such a game. Really, it does not take much, but it takes more than you have. Sorry.</p>';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET hideLocation = 1, respect = respect - 2 WHERE userid = {$userId}");

    print '
        <p>So you think you can do this huh? OK then, you\'re on. You pay the 2 Respect entry fee. Now go find a place to hide!</p>
        <p>Go! Go now!</p>
    ';
}

function hide(Database $db, int $userId, string $hide): void
{
    $rmst = array('/', '.php');
    $hide = str_replace($rmst, '', $hide);

    $db->query("UPDATE users SET hideLocation = '{$hide}' WHERE userid = {$userId}");

    print '<p>Good, you have hidden well. You are hiding in the <a href=\'' . $hide . '.php\'>' . $hide . '</a> area of the game.</p>';
}

function seek(Database $db, Header $headers, array $user, int $userId, string $hide): void
{
    $fee = 550 * $user['hideSearches'];
    if ($user['money'] < $fee) {
        print '<p>You do not have enough cash to seek here. Really, it does not take much, but it takes more than you have. Sorry.</p>';

        $headers->endpage();
        exit;
    }

    $db->query("UPDATE users SET hideSearches = hideSearches + 1, money = money - {$fee} WHERE userid = {$userId}");

    $rmst = array('/', '.php');
    $seek = str_replace($rmst, '', $hide);

    print '<p>You carefully and methodically search the entire area...</p>';

    $rhid = null;
    if (rand(1, 2) == 1) {
        $rhid = mysqli_fetch_assoc($db->query("SELECT userid FROM users WHERE hideLocation = '{$seek}' ORDER BY RAND() LIMIT 1"));
    }

    if ($rhid != null && $rhid['userid'] > 0 && $rhid['userid'] != $userId) {
        print '
            <h6 class=center>Congratulations<br>You won the Hide and Seek!</h6><br><br>
            <p>You found ' . mafioso($rhid['userid']) . ' hiding in the ' . $seek . ' area grabbed their shirtcollar and exposed them to the world!</p>
            <p><a href=\'' . $seek . '.php\'>Return to the ' . $seek . ' area</a></p>
        ';

        $db->query("UPDATE users SET hideLocation = 0 WHERE userid = {$rhid['userid']}");
        logEvent($rhid['userid'], 'You were found in Hide and Seek by ' . mafiosoLight($userId) . '! <a href=\'gameHideSeek.php\'>Play Again</a>');
        $db->query("UPDATE users SET respect = respect + 2, hideWins = hideWins + 1 WHERE userid = {$userId}");
    } else {
        print '
            <p>Sadly, there is no one to find here at this time. There may be someone here - hiding behind some art, or that word over there, but you did not find anything this time.</p>
            <p><a href=\'' . $seek . '.php\'>Return to the ' . $seek . ' area</a></p>
        ';
    }
}

$headers->endpage();
