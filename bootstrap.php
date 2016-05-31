<?php

use CSVTest\Controller\HomeController;
use CSVTest\Controller\UploadController;
use CSVTest\Controller\DownloadController;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => true,
    ],
]);

$container['view'] = function ($c) use ($container) {
    $view = new Twig('views/', [
    ]);
    $view->addExtension(new TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));
    return $view;
};

$container['homeController'] = function($c) use ($container) {
    return new HomeController($container->get('view'));
};

$container['uploadController'] = function($c) use ($container) {
    return new UploadController($container->get('view'));
};

$container['downloadController'] = function($c) use ($container) {
    return new DownloadController($container->get('view'));
};

$app = new App($container);
return $app;
