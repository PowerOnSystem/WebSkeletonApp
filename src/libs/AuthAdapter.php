<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */

namespace App\Libs;
use PowerOn\Authorization\AuthorizationAdapterInterface;
use PowerOn\Authorization\UserCredentials;
use PowerOn\Database\Model;

/**
 * AuthAdapter
 * Adaptador de inicio de sesión configurado para la aplicación.
 *
 * @author Lucas Sosa <sosalucas87@gmail.com>
 */
class AuthAdapter implements AuthorizationAdapterInterface {
  /**
   * Base de datos
   * @var \PowerOn\Database\Model
   */
  private $db;
  
  public function __construct(Model $database) {
    $this->db = $database;
  }
  
  public function login(UserCredentials $credentials) {
    $user = $this->db->select()->from('users')->where(['username' => $credentials->username])->first();
    if ($user && password_verify($credentials->password, $user['password'])) {
        $credentials->setUserData($user);
        $credentials->setUserAccessLevel($user['accessLevel']);
        
        return $credentials;
    }
    
    return FALSE;
  }

  public function logout() {}

  public function pauseSession() {}

  public function resumeSession() {}
  
  public function passwordHasher() {}
}
