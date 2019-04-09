#!/usr/bin/env php
<?php

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
call_user_func(function () {
    /** @var \Interop\Container\ContainerInterface $container */
    $container = require 'config/container.php';

    $app = new \Symfony\Component\Console\Application('Application console');

    $commands = $container->get('config')['console']['commands'];
    foreach ($commands as $command) {
        $app->add($container->get($command));
    }

    $app->run();
});