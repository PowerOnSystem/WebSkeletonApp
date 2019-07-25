<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
use Slim\Http\Request;
use Slim\Http\Response;
use PowerOn\Authorization\Authorization;
use PowerOn\Utility\Session;

/* @var $app Slim\App */
$app->add(function(Request $request, Response $response, $next) {
  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');
  $url = $request->getUri()->getPath();

  if ( $auth->sector($request->getUri()->getPath(), $request) ) {
    
    $arrayToken = $request->getHeader('TokenAuthorization');
      $token = $arrayToken && is_array($arrayToken) 
        ? reset($arrayToken)
        : NULL
      ;
      if ( Session::read('ApiToken') 
              && !in_array($url, ['/api/account/login', '/api/account/logout']) 
              && $token != Session::read('ApiToken') ) {
        return $response->withStatus(401, 'session_end');
      }
      
      return $next($request, $response);
  }
  if ( !in_array($url, ['/api/account/login', '/api/account/logout', '/api/system/data']) && (
        $auth->getStatus() == Authorization::AUTH_STATUS_USER_NOT_FOUND
        || $auth->getStatus() == Authorization::AUTH_STATUS_USER_SESSION_END
        || $auth->getStatus() == Authorization::AUTH_STATUS_USER_SESSION_INACTIVE
      ) 
  ) {
    return $response->withStatus(401, 'session_end');
  }
  
  if ( in_array($url, ['/api/account/login', '/api/account/logout', '/api/system/data']) ) {
      return $next($request, $response);
  }
  
  if ( $auth->getStatus() == Authorization::AUTH_STATUS_SECTOR_LOW_ACCESS_LEVEL) {
    return $response->withStatus(403, 'low_access_level');
  }
  
  return $response->withStatus(404, 'sector_not_found');
});