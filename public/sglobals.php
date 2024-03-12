<?php
session_start();
require_once "../vendor/autoload.php";

use Fmw\Database;
use Fmw\Header;

require_once "global_func.php";
require_once "../config/database.php";
global $_CONFIG;

$db = new Database($_CONFIG['hostname'], $_CONFIG['username'], $_CONFIG['password'], $_CONFIG['database']);
$user = mysqli_fetch_assoc($db->query("SELECT u.*, us.* FROM users u INNER JOIN userstats us WHERE u.userid = {$_SESSION['userId']}"));
$query = $db->query("SELECT conf_name, conf_value FROM settings");

$settings = array();
while ($row = mysqli_fetch_assoc($query)) {
    $settings[$row['conf_name']] = $row['conf_value'];
}

$headers = new Header($db, $user, $settings);

if ($_SESSION['loggedin'] == 0) {
    header("Location: index.php");
    exit;
}

if ($user['force_logout']) {
    $db->query("UPDATE users SET force_logout = 0 WHERE userid = {$user['userid']}");

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
}

if ($user['rankCat'] != 'Staff') {
    print "Access Denied";

    $headers->endPage();
    exit;
}

checkLevel();

$fm = moneyFormatter($user['money']);
$bm = moneyFormatter($user['money'] + $user['moneyChecking']);
$cm = moneyFormatter($user['respect'], '');
$lv = date('F j, Y, g:i a', $user['trackActionTime']);

$staffPage = 1;
$userId = $_SESSION['userId'];

$headers->startHeaders();
$headers->userData();
$headers->staffMenuArea();
