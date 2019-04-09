<?php

require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Component\Console\Helper\HelperSet;

$dbParams = include 'migrations-db.php';

$connection = DriverManager::getConnection($dbParams);

return new HelperSet([
    'db' => new ConnectionHelper($connection),
]);