<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use PowerOn\Authorization\Authorization;
use Slim\Container;

/* @var $app Slim\App */
$container = $app->getContainer();

// Register component on container
$container['auth'] = function (Container $container) {
  $settings = $container->get('settings')['authorization'];
  $permissions = include '../src/settings/permissions.php';
  return new Authorization($settings, $permissions);
};