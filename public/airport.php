<?php

use Fmw\Airport;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 0);

$action         = isset($_GET['action'])    ? mysql_tex($_GET['action'])    : '';
$falsePassport  = isset($_GET['fp'])        ? mysql_num($_GET['fp'])        : 0;
$class          = isset($_POST['class'])    ? mysql_tex($_POST['class'])    : '';
$destination    = isset($_POST['location']) ? mysql_num($_POST['location']) : 0;
$airport        = new Airport($application);

if (isset($_GET['destination']) && $_GET['destination'] > 0) {
    $destination = $_GET['destination'];
}

try {
    switch ($action) {
        case "fly":
            $airport->fly($class, $destination, $falsePassport);
            break;
        default:
            $airport->index();
            break;
    }
} catch (Exception $e) {
    $application->logger->error($e->getMessage());
}
