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
use PowerOn\Authorization\UserCredentials;
use PowerOn\Authorization\AuthorizationException;
use \App\Libs\AuthAdapter;
use PowerOn\Utility\Session;
use PowerOn\Validation\Validator;

/* @var $app App */
$app->post('/api/account/login', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */
  $status = 'success';
  $message = NULL;
  $user = NULL;

  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');

  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');

  $credentials = new UserCredentials(
      $auth->getUserCredentials() && $auth->getUserCredentials()->isVerified()
          ? $auth->getUserCredentials()['username'] 
          : $request->getParsedBodyParam('username'),
      $request->getParsedBodyParam('password')
  );

  $adapter = new AuthAdapter($database);
  $auth->registerAdapter($adapter);

  try {
    $auth->login($credentials);
    if ( $auth->isValid() ) {
      $user = $auth->getUserCredentials();
      
      $token = uniqid();
      Session::write('ApiToken', $token);
      $message = 'Bienvenido ' . $user['firstName'] . ' ' . $user['lastName'];
    }
  } catch (AuthorizationException $e) {
    $message = $e->getMessage();
    $status = 'error';
  }

  return $response->withJson([
    'status' => $status,
    'message' => $message,
    'data' => $user 
      ? [
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'username' => $user['username'],
        'accessLevel' => $user['accessLevel'],
        'token' => $token,
        'fullName' => $user['firstName'] . ' ' . $user['lastName'],
        'image' => $user['image']
      ]
      : NULL
  ], NULL, JSON_NUMERIC_CHECK);
});

$app->get('/api/account/logout', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */

  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');

  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');
  $adapter = new AbsolAuthAdapter($database);
  $auth->registerAdapter($adapter);

  $auth->logout();
  
  return $response->withJson([
    'status' => 'success'
  ]);
});

$app->get('/api/account', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */

  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');
  $logged = $auth->getUserCredentials();
  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');
  $user = $database->getByIdFrom('users', $logged['id']);
  
  return $response->withJson([
    'status' => 'success',
    'message' => NULL,
    'data' => [
      'firstName' => $user['firstName'],
      'lastName' => $user['lastName'],
      'username' => $user['username'],
      'accessLevel' => $user['accessLevel'],
      'address' => $user['address'],
      'phone' => $user['phone'],
      'tin' => $user['tin'],
      'email' => $user['email'],
      'logo' => $user['logo'],
      'created' => $user['created']
    ]
  ], NULL, JSON_NUMERIC_CHECK);
});

$app->post('/api/account/recover', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */
  $username = $request->getParsedBodyParam('username');
  
  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');
  $user = $database->select(['id', 'firstName', 'lastName', 'companyId'])->from('users')->where(['username' => $username])->first();
    
  if ( !$user ) {
    throw new \Exception(sprintf('No existe un usuario con el nombre "%s"', $username));
  }
  
  $userAdmin = $database
    ->select('id')
    ->from('users')
    ->where(['accessLevel >=' => 9, 'username !=' => $username, 'companyId' => $user['companyId']])
    ->first()
  ;
  
  if ( !$userAdmin ) {
    throw new \Exception('No hay un administrador disponible, se envió un informe a soporte técnico.');
  }
  
  $recoverRequest = $database
    ->select('id')
    ->from('notifications')
    ->where([
      'userId' => $userAdmin['id'], 
      'url' => '/account/recover/' . $user['id'], 
      ['readDate IS' => NULL, 'OR', 'readDate <' => date('Y-m-d H:i:s', time() + 86400)]
    ])
    ->first()
  ;
  
  var_dump($database->debug(\PowerOn\Database\Model::DEBUG_QUERIES));
  die();
  
  if ( $recoverRequest ) {
    throw new \Exception('Su solicitud de recuperación de contraseña ya fue enviada');
  }

  $database
    ->insert('notifications')->values([
      'userId' => $userAdmin['id'],
      'title' => 'Solicitud de restablecimiento de contraseña',
      'description' => 'El usuario ' . $user['firstName'] . ' ' . $user['lastName'] 
        . ' (' . $username . ') solicitó el restablecimiento de su contraseña.',
      'url' => '/account/recover/' . $user['id'],
      'createdDate' => date('Y-m-d H:i:s')
    ])
    ->execute();
    
  return $response->withJson([
    'status' => 'success',
    'message' => 'Un administrador del sistema fue notificado para asignarle una nueva contraseña.'
  ]);
});

$app->post('/api/account/edit/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */

  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');
  
  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');
  $user = $auth->getUserCredentials();
  $data = $request->getParsedBody();
  $file = $request->getUploadedFiles();
  $data['logo'] = key_exists('logo', $_FILES) ? $_FILES['logo'] : NULL;
  
  $validator = new Validator();

  $validator->add('firstName', 'string_allow', ['alpha', 'spaces', 'dots', 'commas']);
  $validator->add('firstName', 'range_length', [3, 30]);
  $validator->add('firstName', 'required', TRUE);
  
  $validator->add('lastName', 'string_allow', ['alpha', 'spaces', 'dots', 'commas']);
  $validator->add('lastName', 'range_length', [3, 30]);
  $validator->add('lastName', 'required', TRUE);
  
  $validator->add('company', 'string_allow', ['alpha', 'spaces', 'dots', 'commas']);
  $validator->add('company', 'range_length', [3, 50]);
  
  $validator->add('address', 'string_allow', ['alpha', 'spaces', 'dots', 'commas', 'numbers', 'mid_strips']);
  $validator->add('address', 'range_length', [3, 150]);
  
  $validator->add('phone', 'string_allow', ['spaces', 'commas', 'numbers', 'mid_strips', 'symbols']);
  $validator->add('phone', 'range_length', [3, 100]);
  
  $validator->add('tin', 'string_allow', ['alpha', 'spaces', 'numbers', 'mid_strips']);
  $validator->add('tin', 'range_length', [3, 40]);
  
  $validator->add('logo', 'upload', TRUE, Rule::ERROR, 'Debe ser un archivo válido');
  $validator->add('logo', 'extension', ['jpg', 'jpeg', 'gif', 'png'], Rule::ERROR, 
          'El logotipo debe contener una extensión válida (jpg, jpeg, png o gif)');
  
  $validator->add('email', 'email', TRUE);
  $validator->add('email', 'required', TRUE);
  $validator->add('email', 'unique', $database->select(['email'])->from('users')->where(['id !=' => $user['id']])
          ->all()->column('email'),
          Rule::ERROR, 'El email ya se encuentra registrado.');

  $validator->add('username', 'required', TRUE);
  $validator->add('username', 'range_length', [4, 12]);
  $validator->add('username', 'string_allow', ['alpha', 'numbers', 'spaces', 'low_strips', 'mid_strips']);
  $validator->add('username', 'unique', $database->select(['username'])->from('users')->where(['id !=' => $user['id']])
          ->all()->column('username'), 
          Rule::ERROR, 'El nombre de usuario se encuentra en uso.');
  
  $status = 'error';
  
  if ( $validator->validate($data) ) {
    $update = [
      'firstName' => mb_convert_case(mb_convert_case($data['firstName'], MB_CASE_LOWER, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
      'lastName' => mb_convert_case(mb_convert_case($data['lastName'], MB_CASE_LOWER, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
      'username' => mb_convert_case($data['username'], MB_CASE_LOWER, 'UTF-8'),
      'email' => mb_convert_case($data['email'], MB_CASE_LOWER, 'UTF-8'),
      'company' => $data['company'],
      'tin' => $data['tin'],
      'address' => $data['address'],
      'phone' => $data['phone']
    ];
    
    if ($file) {
      $path = ROOT . DS . '..' . DS . 'assets' . DS . 'img' . DS . 'users';
      $update['logo'] = moveUploadedFile($path, $file['logo']);
      createThumbnail($path . DS . $update['logo'], $path, 450);
    } else if (key_exists('original_logo', $data)) {
      $update['logo'] = $data['original_logo'] && !in_array($data['original_logo'], ['null'. 'false', 'undefined']) 
        ? $data['original_logo']
        : NULL
      ;
    }
    
    $database->update('users')->set($update)->execute();
    
    $status = 'success';
    $message = 'Usuario actualizado correctamente';
  } else {
    $message = implode(', ', $validator->getErrors());
  }
  
  $updatedUser = $database->getByIdFrom('users', $user['id']);
  
  return $response->withJson([
    'status' => $status,
    'message' => $message,
    'data' => $status == 'success'
      ? [
        'firstName' => $updatedUser['firstName'],
        'lastName' => $updatedUser['lastName'],
        'username' => $updatedUser['username'],
        'company' => $updatedUser['company'],
        'address' => $updatedUser['address'],  
        'phone' => $updatedUser['phone'],
        'tin' => $updatedUser['tin'],
        'logo' => $updatedUser['logo'],
        'accessLevel' => $updatedUser['accessLevel'],
        'token' => Session::read('AbsolApiToken')
      ]
      : NULL
  ], NULL, JSON_NUMERIC_CHECK);
});

$app->post('/api/account/add', function (Request $request, Response $response, array $args){ 
  $data = $request->getParsedBody();
  
  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');
  
  $validator = new Validator();
  
  $validator->add('terms', 'required', TRUE, Rule::ERROR, 'Debe aceptar los términos y condiciones del servicio.');
  
  $validator->add('firstName', 'string_allow', ['alpha', 'spaces', 'dots', 'commas']);
  $validator->add('firstName', 'range_length', [3, 25]);
  $validator->add('firstName', 'required', TRUE);
  
  $validator->add('lastName', 'string_allow', ['alpha', 'spaces', 'dots', 'commas']);
  $validator->add('lastName', 'range_length', [3, 25]);
  $validator->add('lastName', 'required', TRUE);
  
  $validator->add('email', 'email', TRUE);
  $validator->add('email', 'required', TRUE);
  $validator->add('email', 'unique', $database->select(['email'])->from('users')->all()->column('email'),
          Rule::ERROR, 'El email ya se encuentra registrado.');
  
  $validator->add('password', 'min_length', 6);
  $validator->add('password', 'required', TRUE);
  
  $validator->add('username', 'required', TRUE);
  $validator->add('username', 'range_length', [4, 12]);
  $validator->add('username', 'string_allow', ['alpha', 'numbers', 'spaces', 'low_strips', 'mid_strips']);
  $validator->add('username', 'unique', $database->select(['username'])->from('users')->all()->column('username'), 
          Rule::ERROR, 'El nombre de usuario se encuentra en uso.');
  
  $status = 'error';
  if ( $validator->validate($data) ) {
    $database
      ->insert('users')
      ->values([
        'firstName' => mb_convert_case(mb_convert_case($data['firstName'], MB_CASE_LOWER, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
        'lastName' => mb_convert_case(mb_convert_case($data['lastName'], MB_CASE_LOWER, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
        'username' => mb_convert_case($data['username'], MB_CASE_LOWER, 'UTF-8'),
        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        'email' => mb_convert_case($data['email'], MB_CASE_LOWER, 'UTF-8'),
        'created' => date('Y-m-d H:i:s'),
        'accessLevel' => 1
      ])
      ->execute()
    ; 
    
    $status = 'success';
    $message = NULL;
  } else {
    $message = implode(', ', $validator->getErrors());
  }
  return $response->withJson([
    'status' => $status,
    'message' => $message
  ], NULL, JSON_NUMERIC_CHECK);
});

$app->get('/api/account/status', function (Request $request, Response $response, array $args) {
  /* @var $this Slim\Container */

  /* @var $database PowerOn\Database\Model */
  $database = $this->get('database');

  /* @var $auth \PowerOn\Authorization\Authorization */
  $auth = $this->get('auth');
  $user = $auth->getUserCredentials();
  
  $quantity = $database->select()->from('notifications')->where(['userId' => $user['id'], 'readDate IS' => NULL])->all()->count();
  $quantitySession = Session::read('AbsolNotificationsCount');
  $show = $quantity 
    && (($quantitySession && $quantitySession < $quantity) || !$quantitySession);
  $message = NULL;
  
  if ($show) {
    $message = 'Tiene (' . $quantity . ') notificaci' . ($quantity > 1 ? 'ones' : 'ón');
  } 
  Session::write('AbsolNotificationsCount', $quantity);
  
  return $response->withJson([
    'status' => 'success',
    'message' => $message,
    'data' => [
      'quantity' => $quantity
    ]
  ], NULL, JSON_NUMERIC_CHECK);
});
