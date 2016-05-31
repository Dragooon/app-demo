<?php

require_once 'vendor/autoload.php';

/** @var \Slim\App $app */
$app = require_once 'bootstrap.php';

$app->get('/', 'homeController')->setName('home');

$app->post('/upload', 'uploadController')->setName('upload');

$app->get('/download/{code}', 'downloadController')->setName('download');

$app->run();
