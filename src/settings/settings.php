<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Database settings
        'database' => [
            'debug' => true,
            'host' => 'localhost',
            'dbname' => 'unitest',
            'port' => 3306,
            'username' => 'root',
            'password' => 'marcos6745',
            'containReferenceSuffix' => 'Id'
        ],
        
        // Autorizador
        'authorization' => [
            'login_session_time' => 28800,
            'login_session_inactive_time' => 3600,
            'strict_mode' => true
        ],
        // Monolog settings
        'logger' => [
            'name' => 'solar-fight-club',
            'path' => getenv('docker') ? 'php://stdout' : __DIR__ . '/../logs/solar-fight-club.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];
