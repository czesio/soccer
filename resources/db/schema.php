<?php

$schema = new \Doctrine\DBAL\Schema\Schema();

$season = $schema->createTable('season');
$season->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$season->addColumn('created_at', 'datetim');
$season->setPrimaryKey(array('id'));

$game = $schema->createTable('game');
$game->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
$game->addColumn('created_at', 'datetim');
$game->addColumn('season_id', 'integer');
$game->setPrimaryKey(array('id'));

return $schema;
