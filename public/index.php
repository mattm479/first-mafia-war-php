<?php

use Fmw\Template;

require_once '../vendor/autoload.php';

$templateDir = __DIR__ . '/../templates';
$template = new Template($templateDir);

$template->render('login.html.twig');
