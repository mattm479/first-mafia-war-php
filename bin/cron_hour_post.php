<?php
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
require_once "../public/global_func.php";
$db = new Database($config['database']);

// Giovane Gear
$qgio = $db->query("SELECT userid FROM users WHERE `rank` = 'Giovane'");
while ($rg = mysqli_fetch_assoc($qgio)) {
    $getgear = rand(1, 48);
    if ($getgear == 1) {
        $rank = rand(2, 5);
        $bonus = itemRandom($rank);
        itemAdd($bonus, 1, 0, $rg['userid'], 0);
    }

    if ($getgear == 2) {
        $rank = rand(5, 8);
        $bonus = itemRandom($rank);
        itemAdd($bonus, 1, 0, $rg['userid'], 0);
    }

    if ($getgear == 3) {
        $db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$rg['userid']}");
    }
}

// Street Fighting
$qsf = $db->query("SELECT sfID, sfTitle FROM streetFight WHERE sfStart = 2");
while ($rsf = mysqli_fetch_assoc($qsf)) {
    newsPost(1, "The <a href='../public/streetfight.php'>{$rsf['sfTitle']}</a> street fight begins in about an hour.");
}

$qsf = $db->query("SELECT sfID, sfTitle, sfGift, sfPrize FROM streetFight WHERE sfEnd = 1");
while ($rsf = mysqli_fetch_assoc($qsf)) {
    $db->query("DELETE FROM inventory WHERE inv_itemid = 31");
    $comment = "The street fight <em>{$rsf['sfTitle']}</em> is over. ";
    $qg = $db->query("SELECT userid FROM users WHERE attacksID = {$rsf['sfID']} ORDER BY attacks DESC LIMIT 1");
    while ($rg = mysqli_fetch_assoc($qg)) {
        itemAdd($rsf['sfPrize'], 1, 0, $rg['userid'], 0);
        logEvent($rg['userid'], "You won the Grand Prize in the street fight - " . itemInfo($rsf['sfPrize']) . ".");
        $comment .= mafiosoName($rg['userid']) . " was the grand prize winner (" . itemName($rsf['sfPrize']) . ")";
        $db->query("UPDATE streetFight SET sfPrizeWinner = {$rg['userid']} WHERE sfID = {$rsf['sfID']}");
    }

    $qw = $db->query("SELECT userid FROM users WHERE attacksID = {$rsf['sfID']} ORDER BY attacks DESC LIMIT 3");
    while ($rw = mysqli_fetch_assoc($qw)) {
        itemAdd($rsf['sfGift'], 1, 0, $rw['userid'], 0);
        logEvent($rw['userid'], "You won a Gift in the street fight - " . itemInfo($rsf['sfGift']) . ".");
        $comment .= ", " . mafiosoName($rw['userid']);
    }

    $comment .= " each won a " . itemName($rsf['sfGift']) . ".";
    $db->query("UPDATE streetFight SET sfComment = '{$comment}' WHERE sfID = {$rsf['sfID']}");
    $db->query("UPDATE users SET attacks = 0, attacksID = 0 WHERE attacksID = {$rsf['sfID']}");
    newsPost(1, "{$comment}");
}

$db->query("UPDATE streetFight SET sfStart = sfStart - 1 WHERE sfSTart > 0");
$db->query("UPDATE streetFight SET sfEnd=sfEnd - 1 WHERE sfStart = 0 AND sfEnd > 0");

// Family Crime  3am
if (date('H') == 3) {
    $qca = $db->query("SELECT faID, faCrime, faFamily FROM familyActiveCrime WHERE faDaysLeft <= 1");
    while ($rca = mysqli_fetch_assoc($qca)) {
        $rcr = mysqli_fetch_assoc($db->query("SELECT fcID, fcGainCash, fcGainRespect, fcName FROM familyCrime WHERE fcID = {$rca['faCrime']}"));
        $GainCash = round((rand(8, 11) * $rcr['fcGainCash']) / 10);
        $db->query("UPDATE family SET famVaultCash = famVaultCash + {$GainCash}, famRespect = famRespect + {$rcr['fcGainRespect']} WHERE famID = {$rca['faFamily']}");
        $db->query("INSERT INTO gangevents (gevGANG, gevTIME, gevTEXT) VALUES({$rca['faFamily']}, unix_timestamp(), 'Your family succeeded at crime! {$rcr['fcName']} gained you '" . moneyFormatter($rcr['fcGainCash']) . "' and {$rcr['fcGainRespect']} Respect.'");
        $db->query("DELETE FROM familyActiveCrime WHERE faID = {$rca['faID']}");
    }

    $db->query("UPDATE familyActiveCrime SET faDaysLeft = faDaysLeft - 1");
}

// Sicilian Thanksgiving 5am
if (date('H') == 5) {
    $qcho = $db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 625");
    while ($rcho = mysqli_fetch_assoc($qcho)) {
        itemAdd(18, 1, 0, $rcho['inv_userid'], 0);
        itemAdd(16, 1, 0, $rcho['inv_userid'], 0);
        itemAdd(9, 1, 0, $rcho['inv_userid'], 0);
        logEvent($rcho['inv_userid'], "Ceres left you with enough cereal for a half-pack of beer.");
    }
}

// Early Mentor Education 6am
if (date('H') == 6) {
    $query = $db->query("SELECT u.userid, u.cdays, u.course FROM users u LEFT JOIN coursesdone c ON u.userid = c.userid WHERE c.courseid = 28 AND u.course > 0");
    while ($row = mysqli_fetch_assoc($query)) {
        $db->query("UPDATE users SET cdays = cdays - 1 WHERE userid = {$row['userid']}");
        if ($row['cdays'] <= 1) {
            educationFinish($row['course'], $row['userid']);
        }
    }
}

// Halloween at 7am
if (date('H') == 7) {
    $qcho = $db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 603");
    while ($rcho = mysqli_fetch_assoc($qcho)) {
        $rgr = mysqli_fetch_assoc($db->query("SELECT itmid FROM items WHERE itmLevel IN (8, 9) ORDER BY RAND() LIMIT 1"));
        itemAdd($rgr['itmid'], 1, 0, $rcho['inv_userid'], 0);
        logEvent($rcho['inv_userid'], 'You picked up a lovely ' . itemInfo($rgr['itmid']) . ' from your dead ancestors this morning.');
    }
}

//Tokens for a sweet ride 8am
if (date('H') == 8) {
    $query = $db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 81");
    while ($row = mysqli_fetch_assoc($query)) {
        $db->query("UPDATE users SET respect = respect + 1 WHERE userid = {$row['inv_userid']}");
        logEvent($row['inv_userid'], "You gain Respect as you drive in the morning sun.");
    }
}

// Daily Raffle 11am and noon
if (date('H') == 11) {
    $query = $db->query("SELECT raItem FROM raffle WHERE raDaysLeft = 1");
    while ($row = mysqli_fetch_assoc($query)) {
        newsPost(1, "Raffle drawing for " . itemName($row['raItem']) . " in about an hour!");
    }
}

if (date('H') == 12) {
    $db->query("UPDATE raffle SET raDaysLeft = raDaysLeft - 1");

    $query = $db->query("SELECT raID, raItem FROM raffle WHERE raDaysLeft = 0");
    while ($row = mysqli_fetch_assoc($query)) {
        $q2 = $db->query("SELECT rtPurchaser FROM raffleTicket WHERE rtRaffle = {$row['raID']} ORDER BY RAND() LIMIT 1");
        $r2 = mysqli_fetch_assoc($q2);
        itemAdd($row['raItem'], 1, 0, $r2['rtPurchaser'], 0);
        logEvent($r2['rtPurchaser'], "You won a " . iteminfo($row['raItem']) . " at todays raffle!");
        newsPost(1, mafiosoName($r2['rtPurchaser']) . " won the " . itemName($row['raItem']) . " in today's Raffle!");
    }

    $qdel = $db->query("SELECT raID FROM raffle WHERE raDaysLeft <= 0");
    while ($rdel = mysqli_fetch_assoc($qdel)) {
        $db->query("DELETE FROM raffleTicket WHERE rtRaffle = {$rdel['raID']}");
    }

    $db->query("UPDATE raffle SET raDaysLeft = 30 WHERE raDaysLeft = 0");
}

// Kansas City Shuffle 1pm
if (date('H') == 13) {
    $query = $db->query("SELECT userid FROM coursesdone WHERE courseid = 24");
    while ($row = mysqli_fetch_assoc($query)) {
        $db->query("UPDATE users SET visits = visits - 1 WHERE visits >= 0 AND userid = {$row['userid']}");
    }
}

// Afternoon Tea 3pm
if (date('H') == 15) {
    $query = $db->query("SELECT userid FROM users WHERE donatordays > 0");
    while ($row = mysqli_fetch_assoc($query)) {
        itemAdd(73, 1, 0, $row['userid'], 0);
        itemAdd(68, 1, 0, $row['userid'], 0);
        logEvent($row['userid'], "You enjoy some afternoon tea.");
    }
}

// Job Results and Marina City Building 5pm
if (date('H') == 17) {
    $qmar = $db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 629");
    if ($rmar = mysqli_fetch_assoc($qmar)) {
        $db->query("UPDATE users SET visits = visits - 1 WHERE visits >= 0 AND userid = {$rmar['inv_userid']}");
    }

    $db->query("UPDATE users u LEFT JOIN jobranks jr ON u.jobrank = jr.jrID SET u.money = u.money + jr.jrPAY WHERE u.jobrank > 0");
    $db->query("UPDATE userstats us LEFT JOIN users u ON u.userid = us.userid LEFT JOIN jobranks jr ON u.jobrank = jr.jrID SET us.strength = us.strength + jr.jrSTRG, us.agility = us.agility + jr.jrAGIG, us.labour = us.labour + jr.jrLABOURG, us.IQ = us.IQ + jr.jrIQG WHERE u.jobrank > 0");

    $query = $db->query("SELECT userid FROM users WHERE jobrank > 0");
    while ($row = mysqli_fetch_assoc($query)) {
        $getcontact = rand(1, 3);
        if ($getcontact == 3) {
            $contactitems = array(11, 12, 13, 26, 27, 51, 52, 632, 636);
            $randomid = array_rand($contactitems);
            $selectedcontact = $contactitems[$randomid];
            itemAdd($selectedcontact, 1, 0, $row['userid'], 0);
            logEvent($row['userid'], "You met a " . iteminfo($selectedcontact) . " during work today.");
        }
    }
}

// Late Mentor Education 6pm
if (date('H') == 18) {
    $db->query("UPDATE users SET cdays = cdays - 1 WHERE course > 0");

    $query = $db->query("SELECT course, userid FROM users WHERE cdays <= 1 AND course > 0");
    while ($row = mysqli_fetch_assoc($query)) {
        educationFinish($row['course'], $row['userid']);
    }
}

$db->close();