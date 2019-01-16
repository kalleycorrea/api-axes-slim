<?php

namespace src;

function slimConfiguration(): \Slim\Container
{
    $configuration = [
        'settings' => [
            'displayErrorDetails' => getenv('DISPLAY_ERRORS_DETAILS'),
            // Monolog settings
            //'logger' => [
            //    'name' => 'API-Axes-Slim',
            //    'path' => __DIR__ . '/logs/app.log',
            //],
        ],
    ];
    $container = new \Slim\Container($configuration);
    
    return $container;
}
