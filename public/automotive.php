<?php

use Fmw\Automotive;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn=1, $stff=0, $njl=1, $nhsp=1, $nlck=1);

$action     = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$autoId     = isset($_GET['ID']) ? mysql_num($_GET['ID']) : 0;
$automotive = new Automotive($application);

switch($action) {
   case 'buy':
       $automotive->buy($userId, $autoId);
       break;
   case 'sell':
       $automotive->sell($userId, $autoId);
       break;
   default:
       $automotive->index();
       break;
}
