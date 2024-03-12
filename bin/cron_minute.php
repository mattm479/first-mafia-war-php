<?php
require_once "../vendor/autoload.php";

use Fmw\Database;

$config = require "../config/application.php";
$db = new Database($config['database']);

// hospital update
$db->query("UPDATE users SET hospital = hospital - 1 WHERE hospital > 0");
$db->query("UPDATE users SET hospital = 0 WHERE hospital < 1");
$hc = mysqli_num_rows($db->query("SELECT userid FROM users WHERE hospital > 0"));
$db->query("UPDATE settings SET conf_value = {$hc} WHERE conf_name = 'hospital_count'");

// jail update
$db->query("UPDATE users SET jail = jail - 1 WHERE jail > 0");
$db->query("UPDATE users SET jail = 0 WHERE jail < 1");
$jc = mysqli_num_rows($db->query("SELECT userid FROM users WHERE jail > 0"));
$db->query("UPDATE settings SET conf_value = {$jc} WHERE conf_name = 'jail_count'");

$db->close();