<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

/* @var $app Slim\App */
$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
  $settings = $c->get('settings')['logger'];
  $logger = new Logger($settings['name']);
  $logger->pushProcessor(new UidProcessor());
  $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));

  return $logger;
};
