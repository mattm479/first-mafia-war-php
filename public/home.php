<?php

use Fmw\Home;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 0, $nhsp = 0, $nlck = 0);

$notes = isset($_POST['notes']) ? mysql_tex($_POST['notes']) : '';
$update = isset($_POST['update']) ? mysql_num($_POST['update']) : 0;
if ($update == 1) {
    $application->db->query("UPDATE users SET user_notepad = '{$notes}' WHERE userid = {$application->user['userid']}");
}

$home = new Home($application);
$home->render();
