<?php

use Fmw\Visit;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 1);

$action     = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$visit_num  = isset($_GET['visit']) ? mysql_num($_GET['visit']) : 0;
$visit      = new Visit($application, $userId, $action);

switch ($action) {
    case "casino":
        $visit->casino($userId, $visit_num);
        break;
    case "distillery":
        $visit->distillery($userId, $visit_num);
        break;
    case "football":
        $visit->football($userId, $visit_num);
        break;
    case "meigsfield":
        $visit->meigs_field($userId, $visit_num);
        break;
    case "plantation":
        $visit->plantation($userId, $visit_num);
        break;
    case "track":
        $visit->track($userId, $visit_num);
        break;
    case "winery":
        $visit->winery($userId, $visit_num);
        break;
    case "don":
    default:
        $visit->don($userId, $visit_num);
        break;
}
