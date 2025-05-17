<?php

use Fmw\Database;
use Fmw\Header;
use Fmw\Jail;

require_once "globals.php";
global $application;
pagePermission($lgn=1, $stff=0, $njl=0, $nhsp=1, $nlck=0);

$action     = isset($_GET['action']) ? mysql_tex($_GET['action']) : '';
$userId     = isset($_GET['uid']) ? mysql_num($_GET['uid']) : 0;
$respect    = isset($_POST['res']) ? mysql_num($_POST['res']) : 0;
$jail       = new Jail($application);

switch ($action) {
    case "bribe":
        $jail->bribe($userId);
        break;
    case "bust":
        $jail->bust($userId);
        break;
    case "bustdo":
        $jail->do_bust($userId, $respect);
        break;
    default:
        $jail->index();
        break;
}
