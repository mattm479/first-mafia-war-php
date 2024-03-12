<?php

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$tresder = rand(100, 999);
$maxbet = $user['level'] * 125;
$_GET['tresde'] = abs((int)$_GET['tresde']);

print '
    <h3>Welcome to the Roulette at Monte Carlo</h3>
    <p>Pick a number between 0 and 36 to try your luck on the wheel.</p>
';

if (($_SESSION['tresde'] == $_GET['tresde']) || $_GET['tresde'] < 100) {
    print "
        <p>Ready to try your luck? Play today! The maximum bet for your level is " . moneyFormatter($maxbet) . " and the odds are 1 in 36.</p>
        <form action='gameRoulette.php' method='get'>
            Bet: <input type='text' name='bet' value='$750'><br>
            Pick (0-36): <input type='text' name='number' value='18'><br>
            <input type='hidden' name='tresde' value='{$tresder}'>
            <input type='submit' value='Play!!'>
        </form>
    ";

    $headers->endpage();
    exit;
}

$bs = '';
$_SESSION['tresde'] = $_GET['tresde'];
$_GET['bet'] = str_replace($bs, '', $_GET['bet']);
$_GET['number'] = abs((int)$_GET['number']);

if ($_GET['bet']) {
    if ($_GET['bet'] > $user['money']) {
        die("You are trying to bet more than you have.<br /><a href='gameRoulette.php?tresde=$tresder'>&gt; Back</a>");
    } else if ($_GET['bet'] > $maxbet) {
        die("You have gone over the max bet.<br /><a href='gameRoulette.php?tresde=$tresder'>&gt; Back</a>");
    } else if ($_GET['number'] > 36 || $_GET['number'] < 0 || $_GET['bet'] < 0) {
        die("The Numbers are only 0 - 36.<br /><a href='gameRoulette.php?tresde=$tresder'>&gt; Back</a>");
    }

    $slot[1] = rand(0, 36);
    print "
        <p>You place " . moneyFormatter($_GET['bet']) . " onto the table and watch the ball roll and jump around the spinning wheel.</p>
        <p>You see the ball settle in... <strong>$slot[1]</strong></p>
        <p>You bet " . moneyFormatter($_GET['bet']) . " 
    ";

    if ($slot[1] == $_GET['number']) {
        $won = $_GET['bet'] * 37;
        $gain = $_GET['bet'] * 36;

        print "and won " . moneyFormatter($won) . "!! Congratulations!</p>";
    } else {
        $won = 0;
        $gain = -$_GET['bet'];

        print "and lost it all.</p>";
    }

    $db->query("UPDATE users SET money = money + {$gain} where userid = {$userId}");
    $tresder = rand(100, 999);

    print "
        <p>
            <a href='gameRoulette.php?bet={$_GET['bet']}&tresde={$tresder}&number={$_GET['number']}'>Another time, same bet</a> &nbsp;&middot;&nbsp;
            <a href='gameRoulette.php?tresde={$tresder}'>Continue on, but change my bet</a> &nbsp;&middot;&nbsp;
            <a href='explore.php'>That's it, I'm off!</a>
        </p>
    ";
} else {
    print "
        <p>Ready to try your luck? Play today! The maximum bet for your level is " . moneyFormatter($maxbet) . " and the odds are 1 in 36.</p>
        <form action='gameRoulette.php' method='get'>
            Bet: <input type='text' name='bet' value='$750'><br>
            Pick (0-36): <input type='text' name='number' value='18'><br>
            <input type='hidden' name='tresde' value='{$tresder}'>
            <input type='submit' value='Play!!'>
        </form>
    ";
}

$headers->endpage();
