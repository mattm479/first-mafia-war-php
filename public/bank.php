<?php

use Fmw\Bank;

require_once "globals.php";
global $application, $userId;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$action     = isset($_GET['act']) ? mysql_tex($_GET['act']) : '';
$from       = isset($_POST['from']) ? mysql_tex($_POST['from']) : '';
$to         = isset($_POST['to']) ? mysql_tex($_POST['to']) : '';
$amount     = isset($_POST['amount']) ? mysql_num($_POST['amount']) : 0;
$courseDone = mysqli_fetch_assoc($application->db->query("SELECT userid FROM coursesdone WHERE courseid = 32 AND userid = {$application->user['userid']}"));
$invest     = (isset($courseDone['userid']) && $courseDone['userid'] == $application->user['userid']) ? 'yes' : 'no';
$bank       = new Bank($application);

switch ($action) {
    case "clear":
        $bank->clear($userId);
        break;
    case "transfer":
        $bank->transfer($userId, $amount, $from, $to, $invest);
        break;
    default:
        $bank->index($invest);
        break;
}
