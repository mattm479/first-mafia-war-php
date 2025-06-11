<?php

use Fmw\Consignment;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$currency = isset($_POST['currency']) ? mysql_tex($_POST['currency']) : '';
$ID = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$AID = isset($_POST['AID']) ? mysql_num($_POST['AID']) : 0;
$price = isset($_POST['price']) ? mysql_num($_POST['price']) : 0;
$consignment = new Consignment($application);

switch ($action) {
    case "add":
        $consignment->add($userId, $ID, $AID, $currency, $price);
        break;
    case "buy":
        $consignment->buy($userId, $ID);
        break;
    case "remove":
        $consignment->remove($userId, $ID);
        break;
    default:
        $consignment->index($userId);
        break;
}
