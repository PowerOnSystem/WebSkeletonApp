<?php
/* 
 * Copyright (C) PowerOn Sistemas - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Lucas Sosa <sosalucas87@gmail.com>, Octubre 2018
 */
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies/database.php';
require __DIR__ . '/../src/dependencies/monolog.php';
require __DIR__ . '/../src/dependencies/authorization.php';

// Register start middlewares
require __DIR__ . '/../src/middlewares/auth.php';

// Register routes
require __DIR__ . '/../src/routes/home.php';
require __DIR__ . '/../src/routes/account.php';

// Register end middlewares
//require __DIR__ . '/../src/middlewares/response.php';

// Run app
$app->run();
