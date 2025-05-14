<?php

return [
  'Database' => [
    'host' => 'localhost',
    'dbname' => '',
    'username' => '',
    'password' => '',
    'charset' => 'utf8mb4',
   // 'primerykey' => ''
    ],
    
  'Session' => [
    'sessionName' => 'test',
    'sessionKey' => 'test',
    'secretKey' => 'gooooooooooooooooooooooooooooooood', //32 char
    'timeout' => 36000,
    ],
    
  'Cookie' => [
    'secretKey' => 'gooooooooooooooooooooooooooooooood',
    'prefix' => 'Woodpecker:',
    'lifetime' => 3600,
    'encrypt' => true,
    'path' => '/',
    'domain' => $_SERVER["HTTP_HOST"] ?? '',
    'secure' => true,
    'httpOnly' => true,
    'sameSite' => 'Strict'
    ]
  ];

?>