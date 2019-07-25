<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */

/**
 * Niveles de acceso:
 * 
 * 1/5 = SIMPLE USER
 * 4 = ADMIN USER
 * 5 = SUPER USER
 */
return [
  '/api/' => true,
  '/api/account' => ['access_level' => 1],
  '/api/account/login' => true,
  '/api/account/logout' => true,
  '/api/account/recover' => true,
  '/api/account/register' => true,
  '/api/account/status' => ['access_level' => 1],
  '/api/account/edit' => ['access_level' => 1],
  '/api/account/delete/*' => ['access_level' => 10],
  
  '/api/notifications' => ['access_level' => 1],  
  '/api/notifications/delete' => ['access_level' => 1],

  '/api/messages' => ['access_level' => 1], 
  '/api/messages/write' => ['access_level' => 1],
  '/api/messages/reply/*' => ['access_level' => 1],
  '/api/messages/delete/*' => ['access_level' => 1]
];
