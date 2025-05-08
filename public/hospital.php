<?php

use Fmw\Hospital;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$userId = isset($_GET['uid']) ? mysql_num($_GET['uid']) : 0;
$hospital = new Hospital($application);

switch ($action) {
    case "laugh":
        $hospital->laugh($userId);
        break;
    case "flowers":
        $hospital->send_flowers($userId);
        break;
    default:
        $hospital->index();
        break;
}
