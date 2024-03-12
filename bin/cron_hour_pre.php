<?php
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
require_once "../public/global_func.php";
$db = new Database($config['database']);

// General Updates 
$db->query("UPDATE users SET gagOrder = gagOrder - 1 WHERE gagOrder > 0 AND location != 42");
$db->query("UPDATE users SET autoOwned = 0 WHERE autoOwned = 1");
$db->query("UPDATE users SET gangLockdown = gangLockdown - 1 WHERE gangLockdown > 0");

$query = $db->query("SELECT userid FROM users WHERE location != 42 AND `rank` IN ('Mafioso', 'Don')");
while ($row = mysqli_fetch_assoc($query)) {
    $db->query("UPDATE users SET visits = visits - 1 WHERE visits > 0 AND userid = {$row['userid']}");
    $db->query("UPDATE users SET moneySavings = moneySavings + (moneySavings * 0.0004) WHERE moneySavings > 0 AND userid = {$row['userid']}");
    $db->query("UPDATE users SET moneyInvest = moneyInvest + (moneyInvest * 0.0006) WHERE moneyInvest > 0 AND userid = {$row['userid']}");
    $db->query("UPDATE users SET moneyTreasury = moneyTreasury + (moneyTreasury * 0.001) WHERE moneyTreasury > 0 AND userid={$row['userid']}");
    $db->query("UPDATE users SET mugGear = mugGear - 1 WHERE mugGear > 3 AND userid = {$row['userid']}");
    $db->query("UPDATE users SET mugRespect = mugRespect - 1 WHERE mugRespect > 3 AND userid = {$row['userid']}");
}

// Set Ranking
$db->query("UPDATE users u LEFT JOIN userstats us ON u.userid = us.userid SET u.comStat = us.strength + us.agility + us.guard");
$db->query("UPDATE users u SET comRank = (SELECT COUNT(us.userid) + 1 FROM userstats us WHERE u.comStat < (us.strength + us.agility + us.guard))");

// The Feds checking for illegal aliens
$query = $db->query("SELECT userid, location, level, residence_1, residence_10, residence_25, residence_50, residence_100, residence_250, residence_500 FROM users WHERE location > level");
while ($row = mysqli_fetch_assoc($query)) {
    $residence = "residence_{$row['location']}";
    $rnd = rand(1, 10);
    if ($rnd > 6) {
        mailMafioso(1, $row['userid'], "Busted by the Feds", "You have been caught without a valid passport in a foreign land. The Federal police force you on a plane back to Sicily. They take your walking around money too. Good thing they did not find out about your house!");
        $db->query("UPDATE users SET money = 0, location = 1 WHERE userid = {$row['userid']}");
    }

    if ($rnd > 8 && $row[$residence] > 0) {
        $npq = $db->query("SELECT hPRICE FROM houses WHERE hID = {$row[$residence]}");
        $np = mysqli_fetch_assoc($npq);
        $sell_price = ($np['hPRICE'] * 0.7);

        $db->query("UPDATE users SET money = {$sell_price}, {$residence} = 0 WHERE userid = {$row['userid']}");
        mailMafioso(1, $row['userid'], "Oops - I spoke too soon", "Sorry, but they did find out about your house and confiscated it. They sold it at auction and send you a part of the proceeds. " . moneyFormatter($sell_price) . " is not much, but it is something.");
    }
}

$db->close();