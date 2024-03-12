<?php

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$property = isset($_GET['property']) ? mysql_num($_GET['property']) : 0;
$sellHouse = isset($_GET['sellhouse']) ? mysql_num($_GET['sellhouse']) : 0;
$residence = "residence_{$application->user['location']}";
$residenceU = "u.residence_{$application->user['location']}";
$esq = $application->db->query("SELECT h.hID, h.hNAME, h.hPRICE, u.userid FROM houses h LEFT JOIN users u ON h.hID = {$residenceU} WHERE u.userid = {$application->user['userid']}");
$es = mysqli_fetch_assoc($esq);

print '
    <h3>Estate Agent</h3>
    <div class=floatright>
        <img src=\'assets/images/photos/livingRoom.jpg\' width=250 height=284 alt=\'living room\'>
    </div>
';

if ($application->user['autoOwned'] == 0) {
    throw new Exception("
        <p>You cannot buy a fine home to live in if you do not have some form of transportation! Yes, yes, I know many city dwellers do not have cars due to the inconvenience, but you are a Mafioso! You inconvenience others!</p>
        <p>As if you would ride a bus with the business stiffs.</p>
        <p><a href='explore.php'>Back to town</a></p>
    ", 500);
}

// Buying Property
if ($property > 0) {
    $npq = $application->db->query("SELECT hID, hNAME, hPRICE FROM houses WHERE hID = {$property}");
    $np = mysqli_fetch_assoc($npq);

    if ($np['hPRICE'] > $application->user['money']) {
        throw new Exception(
        "
            <p>You do not have enough money to buy that property.</p>
            <p><a href='bank.php'>Visit the bank</a> or head <a href='estate.php'>Back to the estate agent</a></p>
        ", 500);
    }

    $sellPrice = ($es['hPRICE'] * 0.8);
    $application->db->query("UPDATE users SET money = money + {$sellPrice}, {$residence} = 1 WHERE userid = {$application->user['userid']}");
    $application->db->query("UPDATE users SET money = money - {$np['hPRICE']}, {$residence} = {$np['hID']} WHERE userid = {$application->user['userid']}");

    print "
        <p>Congratulations on your purchase. You purchased the {$np['hNAME']} for " . moneyFormatter($np['hPRICE']) . "</p>
        <p><a href=\'estate.php\'>Back</a></p>
    ";

    $application->db->query("UPDATE users SET residence_total = (residence_1 * residence_1) + (residence_10 * residence_10) + (residence_25 * residence_25) + (residence_50 * residence_50) + (residence_100 * residence_100) + (residence_250 * residence_250) + (residence_500 * residence_500) WHERE userid = {$userId}");

    setWillpower($userId);
}

// Selling Property
if ($sellHouse > 0) {
    if ($es['hPRICE'] == 0) {
        throw new Exception("<p>You already live in the worst property and cannot sell it.</p><p><a href='estate.php'>Back</a></p>", 500);
    }

    $sellPrice = ($es['hPRICE'] * 0.8);
    $application->db->query("UPDATE users SET money = money + {$sellPrice}, {$residence} = 1 WHERE userid = {$userId}");

    print '
        <p>You sold your ' . $es['hNAME'] . ' for ' . moneyFormatter($sellPrice) . ' and went back to your hovel.</p>
        <p><a href=\'estate.php\'>Back</a></p>
    ';

    $application->db->query("UPDATE users SET residence_total = (residence_1 * residence_1) + (residence_10 * residence_10) + (residence_25 * residence_25) + (residence_50 * residence_50) + (residence_100 * residence_100) + (residence_250 * residence_250) + (residence_500 * residence_500) WHERE userid = {$userId}");

    setWillpower($userId);

    exit;
}

// Viewing Property
if ($es['hPRICE'] == 0) {
    print '<p>Your current property is a simple hovel which holds barely enough willpower to get up in the morning. You really should consider buying a better house if you can afford it.</p>';
} else {
    print '
        <p>Here in ' . locationName($application->user['location']) . ' your current property is the <strong>' . $es['hNAME'] . '</strong> which contains up to ' . moneyFormatter(($es['hID'] * $es['hID'] * 50), "") . ' willpower. To purchase a better home, gather your cash, and click on a residence below.</p>
        <p>If you wish, you may also <a href=\'estate.php?sellhouse=' . $es['hID'] . '\'><strong>sell your residence</strong></a> in this city and move back to your hovel. The sale price will be 80% of the purchase price due to realty fees, taxes, and the generally run down condition of your home.</p>
        <p>You need enough cash on hand to buy a new residence. However, your old residences sale price will be refunded to you when you upgrade.</p>
    ';
}

if (!$es['hID']) {
    $es['hID'] = 1;
}

$hq = $application->db->query("SELECT hID, hNAME, hPRICE FROM houses WHERE hID > {$es['hID']} ORDER BY hID");
while ($row = mysqli_fetch_assoc($hq)) {
    print "<a href='estate.php?property={$row['hID']}'>{$row['hNAME']} (" . moneyFormatter(($row['hID'] * $row['hID'] * 50), "") . ")</a> &nbsp; &nbsp; &middot; " . moneyFormatter($row['hPRICE']) . "<br>";
}

print '<p>When you buy your new home, your willpower maximum may not increase immediately. One must walk through the home for a moment, and savor the experience. Be patient.</p>';
