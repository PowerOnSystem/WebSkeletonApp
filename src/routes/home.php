<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/* @var $app App */
$app->get('/api/', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */

  return $response->withJson([
    'status' => 'success',
    'message' => NULL,
    'data' => []
  ], NULL, JSON_NUMERIC_CHECK);
});
