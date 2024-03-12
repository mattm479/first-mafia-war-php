<?php

use Fmw\Explore;

require_once "globals.php";
global $application;
pagePermission($lgn = 1, $stff = 0, $njl = 1, $nhsp = 1, $nlck = 0);

$explore = new Explore($application);
$explore->render();
