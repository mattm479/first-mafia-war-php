<?php

require_once "globals.php";
global $db, $headers, $user, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$tresder = rand(100, 999);
$maxbet = $user['level'] * 125;
$_GET['tresde'] = abs($_GET['tresde']);

if (($_SESSION['tresde'] == $_GET['tresde']) || $_GET['tresde'] < 100) {
    print "
        <h3>Welcome to the Slot Machines at Monte Carlo</h3>
        <p>Ready to try your luck? Play today!<br>The maximum bet for your level is " . moneyFormatter($maxbet) . " and the odds are 1 in 26 for the big pot.</p>
        <form action='gameSlots.php' method='get'>
            Bet: <input type='text' name='bet' value='\$500'><br>
            <input type='hidden' name='tresde' value='{$tresder}'>
            <input type='submit' value='Play!!'>
        </form>
    ";

    $headers->endpage();
    exit;
}

$_SESSION['tresde'] = $_GET['tresde'];
$bs = '';
$_GET['bet'] = str_replace($bs, "", $_GET['bet']);

print "
    <h3>Welcome to the Slot Machines at Monte Carlo</h3>
    <p>You grab that arm and pull!</p>
";

if ($_GET['bet']) {
    if ($_GET['bet'] > $user['money']) {
        die("You are trying to bet more than you have.<br /><a href='gameSlots.php?tresde=$tresder'>&gt; Back</a>");
    } else if ($_GET['bet'] > $maxbet) {
        die("You have gone over the max bet.<br /><a href='gameSlots.php?tresde=$tresder'>&gt; Back</a>");
    }

    for ($i = 1; $i < 4; $i++) {
        $slot[$i] = rand(0, 9);
    }

    print "
        <p>You place " . moneyFormatter($_GET['bet']) . " in the slot and watch the wheel catch and jump as it spins.</p>
        <p>You see the wheels settle on... &nbsp;-&nbsp; <strong>$slot[1]</strong> &nbsp;-&nbsp; <strong>$slot[2]</strong> &nbsp;-&nbsp; <strong>$slot[3]</strong> &nbsp;-&nbsp;</p>
        <p>You bet " . moneyFormatter($_GET['bet']) . " 
    ";

    if ($slot[1] == $slot[2] && $slot[2] == $slot[3]) {
        $won = $_GET['bet'] * 26;
        $gain = $_GET['bet'] * 25;

        print "and won " . moneyFormatter($won) . " by picking all three numbers!! Congratulations!</p>";
    } else if ($slot[1] == $slot[2] || $slot[2] == $slot[3] || $slot[1] == $slot[3]) {
        $won = $_GET['bet'] * 3;
        $gain = $_GET['bet'] * 2;

        print "and won " . moneyFormatter($won) . " by picking 2 numbers!</p>";
    } else {
        $won = 0;
        $gain = -$_GET['bet'];

        print "and lost it all. You couldn't even get two of the numbers right!</p>";
    }

    $db->query("UPDATE users SET money = money + {$gain} where userid = {$userId}");

    $tresder = rand(100, 999);

    print "
        <p>
            <a href='gameSlots.php?bet={$_GET['bet']}&tresde={$tresder}'>Another time, same bet</a> &nbsp;&middot;&nbsp;
            <a href='gameSlots.php?tresde={$tresder}'>Continue on, but change my bet</a> &nbsp;&middot;&nbsp;
            <a href='explore.php'>That's it, I'm off!</a>
        </p>
    ";
} else {
    print "
        <p>Ready to try your luck? Play today!<br>The maximum bet for your level is " . moneyFormatter($maxbet) . " and the odds are 1 in 26 for the big pot.</p>
        <form action='gameSlots.php' method='get'>
            Bet: <input type='text' name='bet' value='\$500'><br>
            <input type='hidden' name='tresde' value='{$tresder}'>
            <input type='submit' value='Play!!'>
        </form>
    ";
}

$headers->endpage();
