<?php

$autoloadMap = [
    'Woodpecker/WPException' => '/WPException.php',
    'Woodpecker/Cryption' => '/Crypto/Cryption.php',
    'Woodpecker/Router' => '/Router/RouterSystem.php',
    'Woodpecker/Validator' => '/Validator/ValidatorSystem.php',
    'Woodpecker/Facade' => '/Facade/Facade.php',
    
    'Woodpecker/DataBase/Table' => '/DataBase/Table.php',
    'Woodpecker/DataBase/Tools' => '/DataBase/Tools.php',
    'Woodpecker/DataBase/DataBase' => '/DataBase/DataBase.php',
    
    'Woodpecker/Support/Redirect' => '/Support/Helper/Redirect.php',
    'Woodpecker/Support/Response' => '/Support/Helper/Response.php',
    'Woodpecker/Support/Request' => '/Support/Helper/Request.php',
    'Woodpecker/Support/RateLimiter' => '/Support/Helper/RateLimiter.php',
    'Woodpecker/Support/Timer' => '/Support/Helper/Timer.php',
    'Woodpecker/Support/Session' => '/Support/Data/Session.php',
    //'Woodpecker/Support/DataStore' => '/Support/Data/DataStore.php',
    'Woodpecker/Support/number' => '/Support/Value/Number.php',
    'Woodpecker/Support/str' => '/Support/Value/Str.php',
    'Woodpecker/Support/collection' => '/Support/Value/Collection.php',
    'Woodpecker/Support/Cookie' => '/Support/Data/Cookie.php',
];

spl_autoload_register(function($class) use ($autoloadMap) {
    $class = str_replace('\\', '/', trim($class));
    
    if (strpos($class, 'Woodpecker/Controllers/') === 0) {
        $className = substr($class, strlen('Woodpecker/Controllers/'));
        $file = dirname(__DIR__) . '/Controllers/' . $className . '.php';
    } 
    elseif (isset($autoloadMap[$class])) {
        $file = __DIR__ . $autoloadMap[$class];
    }
    elseif (strpos($class, 'WPException') !== false) {
        $file = __DIR__ . '/WPException.php';
    } 
    else {
        error_log("Autoload error: Class not found - $class");
        return;
    }
    
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Autoload error: File not found - $file (for class $class)");
    }
});