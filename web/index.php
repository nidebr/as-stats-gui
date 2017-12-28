<?php

require_once __DIR__ . '/../vendor/autoload.php';
define("ROOT_PATH", __DIR__ . "/..");

// INIT
$app = new Silex\Application();

// LOAD CONFIG CLASS
use Application\ConfigApplication as ConfigApplication;

// LOAD TWIG ENGINE
$app = require __DIR__.'/../app/bootstrap.php';

// LOAD APPLICATION
$app->register(new Application\ApplicationProvider());

$app->run();
