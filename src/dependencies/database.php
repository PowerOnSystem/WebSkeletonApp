<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use PowerOn\Database\Model;
use Slim\Container;

/* @var $app Slim\App */
$container = $app->getContainer();

// Register component on container
$container['database'] = function (Container $container) {
  $settings = $container->get('settings')['database'];
  $service = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s;charset=UTF8;port=%s',
        $settings['host'], 
        $settings['dbname'], 
        $settings['port']
    ),
    $settings['username'], 
    $settings['password']
  );

  return new Model($service, $settings);
};