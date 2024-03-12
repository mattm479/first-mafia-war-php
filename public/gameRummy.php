<?php
// HAWK ENTERPRISES
// http://www.hawkenterprises.org

require_once "globals.php";
global $headers;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$bs = '';
$_POST['bet'] = str_replace($bs, "", $_POST['bet']);

$default = false;
$round1 = false;
$round2 = false;

print "
    <h3>Welcome to Three Card Rummy at Monte Carlo</h3>
    <p>Ready to try your luck? Play today!</p>
";

if (!isset($_POST['mode'])) {
    $default = true;
} else {
    if ($_POST['mode'] == 'round1') {
        $round1 = true;
    }

    if ($_POST['mode'] == 'round2' && isset($_POST['submit'])) {
        $round2 = true;
    }
}

if ($default) {
    print "
        <form action='' method='post'>
            <input type='hidden' name='mode' value='round1'>
            Bet: <input type='text' size='8' name='bet'>
            <input type='submit' name='submit' value='bet'>
        </form>
    ";
}

if ($round1) {
    $card_points = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 10, 10, 1);
    $card_values = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K', 'A');
    $card_order = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 1);
    $card_suits = array('H', 'S', 'C', 'D');
    $card_html = array("H" => '&hearts;', "S" => '&spades;', "C" => '&clubs;', "D" => '&diams;');
    $bet = preg_replace('/[0-9]/', '', $_POST['bet']);
    $deck = array();
    foreach ($card_suits as $idx => $suit) {
        foreach ($card_values as $idxv => $value) {
            $deck[] = array('points' => $card_points[$idxv], 'suit' => $suit, 'value' => $value, 'order' => $card_order[$idxv], 'str' => $card_html[$suit] . $value);
        }
    }

    shuffle($deck);

    $dealer[] = array_pop($deck);
    $dealer[] = array_pop($deck);
    $dealer[] = array_pop($deck);
    foreach ($dealer as $vs) {
        $sortAux[] = $vs['order'];
    }

    array_multisort($sortAux, SORT_ASC, $dealer);
    $player[] = array_pop($deck);
    $player[] = array_pop($deck);
    $player[] = array_pop($deck);
    $sortAux = null;
    foreach ($player as $vs) {
        $sortAux[] = $vs['order'];
    }
    array_multisort($sortAux, SORT_ASC, $player);

    display($dealer, true);

    print "<br><br><br>";

    display($player);

    $_SESSION['p'] = serialize($player);
    $_SESSION['d'] = serialize($dealer);
    $_SESSION['b'] = serialize($bet);

    print "
        <br><br><br>
        <form action='' method='post'>
            <input type='hidden' name='mode' value='round2'>
            <input type='submit' name='submit' value='fold'>
            <input type='submit' name='submit' value='raise'>
        </form>
    ";
}

$fold = false;
if ($round2) {
    if ($fold) {
        print "<p>You have folded and lost $bet.</p>";

        $headers->endpage();
        exit;
    }

// other functions
    $dealer = unserialize($_SESSION['d']);
    $player = unserialize($_SESSION['p']);
    $bet = unserialize($_SESSION['b']);
    $bonusbet = 0;
    $dealer_score = display($dealer);

    print "<br><br><br>";

    $player_score = display($player);
    if ($dealer_score > 20 && $dealer_score > $player_score) {
        print "Dealer does not qualify";
        print "You get your money back";
    } else {
        print "You lose your money";
    }

    if ($dealer_score > $player_score) {
        print "You win.<br>";

        if ($player_score == 0) {
            $bonusbet = $bet * 25;
        } else if ($player_score > 0 && $player_score <= 6) {
            $bonusbet = $bet * 4;
        } else if ($player_score > 6 && $player_score <= 10) {
            $bonusbet = $bet;
        } else if ($player_score > 10 && $player_score <= 12) {
            $bonusbet = $bet * 4;
        }

        if ($player_score == 0) {
            $bet = $bonusbet + ($bet * 4);
        } else if ($player_score > 0 && $player_score <= 5) {
            $bet = $bonusbet + ($bet * 2);
        } else if ($player_score > 5 && $player_score <= 19) {
            $bet = $bonusbet + $bet;
        }

        print "Your Score: $bet";
    }

    print "<br><br><a href='gameRummy.php'>New Game</a>";
}

function display($hand, $isDealer = false)
{
    $score = null;
    for ($i = 0; $i < 3; $i++) {
        if ($isDealer) {
            print "<div style='border:1px solid black;display:inline;padding:8px 4px;margin:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>";
        } else {
            print "<div style='border:1px solid black;display:inline;padding:8px 4px;margin:2px;'>" . $hand[$i]['str'] . "</div>";
        }
    }

    if ($isDealer == false) {
        $score = calculate_score($hand);
    }

    if ($score != null) {
        print " &nbsp; Your score: $score";
    }

    return $score;
}

function calculate_score($hand)
{
    $card2suit = false;
    $card2suitb = false;
    $card3suit = false;
    $pairA = false;
    $pairB = false;
    $triple = false;

    //suited run check
    if ($hand[0]['suit'] == $hand[1]['suit']) {
        $diff = $hand[1]['order'] - $hand[0]['order'];
        if ($diff == 1) {
            $card2suit = true;
        }
    }

    if ($hand[1]['suit'] == $hand[2]['suit']) {
        $diff = $hand[2]['order'] - $hand[1]['order'];
        if ($diff == 1) {
            $card2suitb = true;
        }
    }

    if ($card2suit && $card2suitb) $card3suit = true;
    if ($hand[0]['value'] == $hand[1]['value']) $pairA = true;
    if ($hand[1]['value'] == $hand[2]['value']) $pairB = true;
    if ($pairA && $pairB) $triple = true;
    if ($card3suit || $triple) return 0;
    if ($card2suit || $pairA) return $hand[2]['points'];
    if ($card2suitb || $pairB) return $hand[0]['points'];

    return $hand[0]['points'] + $hand[1]['points'] + $hand[2]['points'];
}

$headers->endpage();
