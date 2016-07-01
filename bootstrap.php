<?php

use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Set up the application in an anonymous function to avoid defining global variables
 *
 * @return ContainerInterface
 */
return call_user_func(function () {
    $config = require __DIR__ . '/config/global.php';

    if (file_exists(__DIR__ . '/config/local.php')) {
        $config = array_replace_recursive($config, require __DIR__ . '/config/local.php');
    }

    $container = new ContainerBuilder();
    $container->addDefinitions($config);
    $container->addDefinitions($config['factories']);

    return $container->build();
});
