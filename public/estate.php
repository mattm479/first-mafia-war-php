<?php

use Fmw\Estate;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$property = isset($_GET['property']) ? mysql_num($_GET['property']) : 0;
$estate = new Estate($application);

switch ($action) {
    case 'buy':
        $estate->buy_house($property);
        break;
    case 'sell':
        $estate->sell_house($property);
        break;
    case 'view':
    default:
        $estate->index();
        break;
}
