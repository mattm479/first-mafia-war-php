<?php
session_start();
require_once "../vendor/autoload.php";

use Fmw\Application;

require_once "global_func.php";
$application = new Application();

if ($_SESSION['loggedin'] == 0) {
    header("Location: index.php");
    exit;
}

if ($application->user['force_logout']) {
    $application->db->query("UPDATE users SET force_logout = 0 WHERE userid = {$application->user['userid']}");

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;
}

if ($application->user['rankCat'] != 'Staff') {
    print "Access Denied";

    $application->header->endPage();
    exit;
}

checkLevel($application);

$fm = moneyFormatter($application->user['money']);
$bm = moneyFormatter($application->user['money'] + $application->user['moneyChecking']);
$cm = moneyFormatter($application->user['respect'], '');
$lv = date('F j, Y, g:i a', $application->user['trackActionTime']);

$staffPage = 1;
$userId = $_SESSION['userId'];

$application->header->startHeaders();
$application->header->userData();
$application->header->staffMenuArea();
