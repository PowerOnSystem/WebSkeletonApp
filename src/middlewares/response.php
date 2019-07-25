<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use Slim\Http\Request;
use Slim\Http\Response;

/* @var $app Slim\App */
$app->add(function(Request $request, Response $response, callable $next) {
  return $next;
});