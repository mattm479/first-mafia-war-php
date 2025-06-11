<?php

use Fmw\StreetFight;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 0);

$action         = isset($_GET['action']) ? mysql_tex($_GET['action']) : "";
$do             = isset($_GET['do']) ? mysql_num($_GET['do']) : 0;
$street_fight   = new StreetFight($application);

switch ($action) {
    case 'join':
        $street_fight->join_fight($userId, $do);
        break;
    default:
        $street_fight->index();
        break;
}
