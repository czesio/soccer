<?php

// Doctrine: DB options
$app['db.options'] = array(
    //'driver' => 'pdo_sqlite',
    //'path' => __DIR__.'/../../var/database.dat',
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'soccer',
    'user' => '',
    'password' => '',
    'charset' => 'utf8'
);
