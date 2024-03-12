<?php
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
require_once "../public/global_func.php";
$db = new Database($config['database']);

// brave update
$db->query("UPDATE users SET brave = brave + (maxbrave / 15) WHERE brave < maxbrave AND donatordays = 0");
$db->query("UPDATE users SET brave = brave + (maxbrave / 10) WHERE brave < maxbrave AND donatordays > 0");
$db->query("UPDATE users SET brave = brave + 2 WHERE level < 20");
$db->query("UPDATE users SET brave = maxbrave WHERE brave > maxbrave");

// hp update
$q = $db->query("SELECT inv_userid FROM inventory WHERE inv_itemid = 631 AND inv_itmexpire > 0");
while ($row = mysqli_fetch_assoc($q)) {
    $db->query("UPDATE users SET hp = hp + (maxhp / 15) WHERE hp < maxhp AND userid = {$row['inv_userid']}");
}
$db->query("UPDATE users SET hp = hp + (maxhp / 15) WHERE hp < maxhp");
$db->query("UPDATE users SET hp = maxhp WHERE hp > maxhp");

// energy update
$db->query("UPDATE users SET energy = energy + (maxenergy / 20) WHERE energy < maxenergy");
$db->query("UPDATE users SET energy = energy + (maxenergy / 15) WHERE energy < maxenergy AND autoOwned > 0");
$db->query("UPDATE users SET energy = energy + 2 WHERE level < 20");
$db->query("UPDATE users SET energy = maxenergy WHERE energy > maxenergy");

// will update
$db->query("UPDATE users SET will = will + (residence_total * 0.1) + 10 WHERE will < maxwill");
$db->query("UPDATE users SET will = maxwill WHERE will > maxwill");

// temporary stat reductions
$query = $db->query("SELECT strength, strengthTemp, userid FROM userstats WHERE strengthTemp != 0");
while ($row = mysqli_fetch_assoc($query)) {
    $str = $row['strengthTemp'] - ($row['strength'] * 0.03);
    if ($row['strengthTemp'] > $row['strength']) {
        $str = round($row['strengthTemp'] * 0.5);
    }

    if ($str < 1) {
        $str = 0;
    }

    $db->query("UPDATE userstats SET strengthTemp = {$str} WHERE userid = {$row['userid']}");
}

$query = $db->query("SELECT agility, agilityTemp, userid FROM userstats WHERE agilityTemp != 0");
while ($row = mysqli_fetch_assoc($query)) {
    $agi = $row['agilityTemp'] - ($row['agility'] * 0.03);
    if ($row['agilityTemp'] > $row['agility']) {
        $agi = round($row['agilityTemp'] * 0.5);
    }

    if ($agi < 1) {
        $agi = 0;
    }

    $db->query("UPDATE userstats SET agilityTemp = {$agi} WHERE userid = {$row['userid']}");
}

$query = $db->query("SELECT guard, guardTemp, userid FROM userstats WHERE guardTemp != 0");
while ($row = mysqli_fetch_assoc($query)) {
    $gua = $row['guardTemp'] - ($row['guard'] * 0.03);
    if ($row['guardTemp'] > $row['guard']) {
        $gua = round($row['guardTemp'] * 0.5);
    }

    if ($gua < 1) {
        $gua = 0;
    }

    $db->query("UPDATE userstats SET guardTemp = {$gua} WHERE userid = {$row['userid']}");
}

//NPC cash bump
$db->query("UPDATE users SET money = money + maxhp + maxhp WHERE `rank` = 'Giovane'");
$db->query("UPDATE users SET hideSearches = hideSearches - 2 WHERE hideSearches > 1");

$db->close();
