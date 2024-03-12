<?php
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
require_once "../public/global_func.php";
$db = new Database($config['database']);

// General updates
$db->query("UPDATE family SET famRespect = famRespect - 1 WHERE famRespect > 1");
$db->query("UPDATE family SET famLockdown = 0 WHERE famLockdown > 0");
$db->query("UPDATE users SET crimes = 0, newVote = 1, respectCut = 0, respectGift = 0, moneySavingsFlag = 0");
$db->query("UPDATE users SET moneyTreasuryFlag = moneyTreasuryFlag - 1 WHERE moneyTreasuryFlag > 0");
$db->query("UPDATE users SET moneyInvestFlag = moneyInvestFlag - 1 WHERE moneyInvestFlag > 0");
$db->query("UPDATE users SET fedjail = fedjail - 1 WHERE fedjail > 0 AND location != 42");
$db->query("UPDATE users SET daysingang = daysingang + 1 WHERE gang > 0");
$db->query("UPDATE users SET donatordays = donatordays - 1 WHERE donatordays > 0");

// Consignment Shop
$db->query("UPDATE conMarket SET cmExpire = cmExpire - 1 WHERE cmExpire > 0");
$db->query("UPDATE conMarket SET cmDaysLeft = cmDaysLeft - 1 WHERE cmDaysLeft > 0");
$db->query("UPDATE conMarket SET cmExpire = 0 WHERE cmDaysLeft < 1 AND cmItem > 600");

// Birthdays and Anniversaries
$query = $db->query("SELECT birthday, trackSignupTime, userid FROM users WHERE rankCat = 'Player'");
while ($row = mysqli_fetch_assoc($query)) {
   $birth = unserialize($row['birthday']);
   $birthday = $birth['mth'] . ', ' . $birth['day'];
   $anniversary = date('F, j', $row['trackSignupTime']);
   $today = date ('F, j');

   if ($birthday == $today) {
       itemAdd(635,1,0, $row['userid'],0);
       logEvent($row['userid'], "Happy Birthday! Please accept this Celebration in honor of your special day.");
       mailMafioso($row['userid'],22,'Birthday Today','It is this players birthday today.  Please let them know we noticed.');
   }

   if ($anniversary == $today) {
       itemAdd(635,1,0, $row['userid'],0);
       logEvent($row['userid'], "Happy Anniversary! Please accept this Celebration in honor of your special day.");
       mailMafioso($row['userid'],22,'Anniversary Today','This player has been with us another year!  Please let them know we noticed.');
   }
}

// Automotive Maintenance
$query = $db->query("SELECT autoMaint, userid, moneyChecking, autoValue FROM users WHERE autoOwned > 0 AND rankCat = 'Player'");
while ($row = mysqli_fetch_assoc($query)) {
   $qanv = $db->query("SELECT inv_itemid, inv_userid FROM inventory WHERE inv_userid = {$row['userid']} AND inv_itemid = 632");
   if (!mysqli_fetch_assoc($qanv)) {
       $newMaint = ($row['autoMaint'] + 1);
       $fee = 0;
       $inv = $db->query("SELECT iv.inv_itemid, i.itmid, i.itmBasePrice FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$row['userid']} AND i.itmtype = 3");
       while ($rinv = mysqli_fetch_assoc($inv)) { $fee += $rinv['itmBasePrice']; }
       $fee += $row['autoValue'];
       $fee = ($fee * $newMaint) / 20;

       if ($row['moneyChecking'] > $fee) {
           $db->query("UPDATE users SET moneyChecking = moneyChecking - {$fee}, autoMaint = {$newMaint} WHERE userid = {$row['userid']}");
       } else {
           $db->query("UPDATE users SET autoOwned = 0, autoMaint = 0, autoValue = 0 WHERE userid = {$row['userid']}");
           $inv = $db->query("SELECT iv.inv_id, i.itmid FROM inventory iv LEFT JOIN items i ON iv.inv_itemid = i.itmid WHERE iv.inv_userid = {$row['userid']} AND i.itmtype = 3");
           while ($rinv = mysqli_fetch_assoc($inv)) { itemDelete($rinv['inv_id'], 1, $row['userid'], 0); }
           logEvent($row['userid'], 'Your car was repossessed to cover maintenance costs');
       }
   }
}

//Reduce duration of specialty items
$db->query("DELETE FROM inventory WHERE inv_itmexpire = 1");
$db->query("UPDATE inventory SET inv_itmexpire = inv_itmexpire - 1 WHERE inv_itmexpire > 0");


// Jail Bail, Jail Bust, Jailer, Hospitaler
$query = $db->query("SELECT userid FROM users ORDER BY jailBails DESC LIMIT 1");
if ($row = mysqli_fetch_assoc($query)) {
   itemAdd(605,1,1, $row['userid'],0);
   logEvent($row['userid'],"As top jail-bailer you get the " . iteminfo(605) . " ");
}

$query = $db->query("SELECT userid FROM users ORDER BY jailBusts DESC LIMIT 1");
if ($row = mysqli_fetch_assoc($query)) {
   itemAdd(604,1,1, $row['userid'],0);
   logEvent($row['userid'],"As top jail-buster you get " . iteminfo(604) . " ");
}

$query = $db->query("SELECT userid FROM users ORDER BY count_hospital DESC LIMIT 1");
if ($row = mysqli_fetch_assoc($query)) {
   itemAdd(608,1,1, $row['userid'],0);
   logEvent($row['userid'],"As top hospitaler you get the " . iteminfo(608) . " ");
}

$query = $db->query("SELECT userid FROM users ORDER BY count_jail DESC LIMIT 1");
if ($row = mysqli_fetch_assoc($query)) {
   itemAdd(609,1,1, $row['userid'],0);
   logEvent($row['userid'],"As top jailer you get the ".iteminfo(609)." ");
}

$db->query("UPDATE users SET jailBusts = 0, jailBails = 0, newsCoffee = 0");
$db->query("UPDATE users SET count_jail = 0, count_hospital = 0");

// Friend and Enemy special items
$rfr = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'friend' GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
if (isset($rfr['clContact'])) {
    itemAdd(606,1,1, $rfr['clContact']);
    $db->query("UPDATE users SET respect = respect + 2 WHERE userid = {$rfr['clContact']}");
    logEvent($rfr['clContact'],"For having the most Friends you gain the " . iteminfo(606) . " ");
}

$ren = mysqli_fetch_assoc($db->query("SELECT clContact, count(clID) AS countValue FROM contactList WHERE clType = 'enemy' GROUP BY clContact ORDER BY countValue DESC LIMIT 1"));
if (isset($ren['clContact'])) {
    itemAdd(607,1,1, $ren['clContact']);
    $db->query("UPDATE users SET respect = respect - 1 WHERE userid = {$ren['clContact']}");
    logEvent($ren['clContact'],"For having the most Enemies you gain the " . iteminfo(607) . " ");
}

// Jardin Exotique Monte Carlo Estate
$qinv = $db->query("SELECT inv_itemid, inv_userid FROM inventory WHERE inv_itemid = 612");
while ($row = mysqli_fetch_assoc($qinv)) {
    $us = mysqli_fetch_assoc($db->query("SELECT exp_needed FROM users WHERE userid = {$row['inv_userid']}"));
    $gain = round(($us['exp_needed'] * rand(5,40)) * 0.01);
    $db->query("UPDATE users SET moneyChecking = moneyChecking + {$gain} WHERE userid = {$row['inv_userid']}");
    logEvent($row['inv_userid'],"You gained " . moneyFormatter($gain) . " from the Casinos yesterday.");
}

// Radiator Building New York Estate
$qinv = $db->query("SELECT inv_itemid, inv_userid FROM inventory WHERE inv_itemid = 613");
while($row = mysqli_fetch_assoc($qinv)) {
    $db->query("UPDATE userstats SET IQ = IQ + 800 WHERE userid = {$row['inv_userid']}");
}

// Llle Dorvale Estate
$qinv = $db->query("SELECT inv_itemid, inv_userid FROM inventory WHERE inv_itemid = 614");
while ($row = mysqli_fetch_assoc($qinv)) {
    itemAdd(30,1,0, $row['inv_userid'],0);
    logEvent($row['inv_userid'],"You smuggled another " . itemInfo(30) . " across the border.");
}

// Cerro El Avila Estate
$qinv = $db->query("SELECT inv_itemid, inv_userid FROM inventory WHERE inv_itemid = 615");
while ($row = mysqli_fetch_assoc($qinv)) {
    itemAdd(46,1,0, $row['inv_userid'],0);
    logEvent($row['inv_userid'],"You gained another " . itemInfo(46) . " to protect your home.");
}

// Donation Monthly cleanup
if (date('d') == 01) {
    $db->query("UPDATE users SET donatedMLast = donatedM, donatedM = 0");
}

// Donation Daily cleanup
$qdon = $db->query("SELECT userid FROM users WHERE donatordays <= 0 AND `rank` = 'Mafioso'");
while ($row = mysqli_fetch_assoc($qdon)) {
    $db->query("DELETE FROM contactList WHERE clSource = {$row['userid']}");
    $db->query("UPDATE users SET moneyChecking = moneyChecking + moneyTreasury WHERE userid = {$row['userid']}");
    $db->query("UPDATE users SET moneyTreasury = 0 WHERE userid = {$row['userid']}");
}

// Log Cleanup
$onedayago = time() - (24 * 60 * 60);
$threedaysago = time() - (3 * 24 * 60 * 60);
$sevendaysago = time() - (7 * 24 * 60 * 60);
$twentyonedaysago = time() - (21 * 24 * 60 * 60);
$thirtydaysago = time() - (30 * 24 * 60 * 60);
$sixtydaysago = time() - (60 * 24 * 60 * 60);
$ninetydaysago = time() - (90 * 24 * 60 * 60);
$db->query("DELETE FROM logsAttacks WHERE laTime < {$thirtydaysago}");
$db->query("UPDATE logsAttacks SET laLogLong = '' WHERE laTime < {$sevendaysago}");
$db->query("DELETE FROM logsEvents WHERE leTime < {$thirtydaysago}");
$db->query("DELETE FROM logsItems WHERE liTime < {$thirtydaysago}");
$db->query("DELETE FROM logsWealth WHERE lwTime < {$thirtydaysago}");
$db->query("DELETE FROM mail WHERE mail_time < {$threedaysago} AND mail_directory = 'Delete'");
$db->query("DELETE FROM mail WHERE mail_time < {$sevendaysago} AND mail_directory = 'Inbox'");
$db->query("DELETE FROM mail WHERE mail_time < {$thirtydaysago} AND mail_directory = 'General'");
$db->query("DELETE FROM gangevents WHERE gevTIME < {$sixtydaysago}");
$db->query("DELETE FROM stafflog WHERE time < {$ninetydaysago}");
$db->query("DELETE FROM news WHERE newsTime < {$onedayago}");

// Mafioso Inactive shift
$db->query("UPDATE users SET `rank` = 'Inattivo' WHERE `rank` = 'Mafioso' AND trackActionTime < {$twentyonedaysago}");

$db->close();