<?php
//autoloader
$namespaces = [
   "Woodpecker/WPException" => "/WPException.php",
    "Woodpecker/Crypto" => "/Crypto/Cryption.php",
    "Woodpecker/Filemanager" => "/FileManage/FileManager.php",
    "Woodpecker/DataBase/Table" => "/DataBase/Table.php",
    "Woodpecker/DataBase/Tools" => "/DataBase/Tools.php",
    "Woodpecker/DataBase/DataBase" => "/DataBase/DataBase.php",
    "Woodpecker/Router" => "/Router/RouterSystem.php",
    "Woodpecker/Validator" => "/Validator/ValidatorSystem.php",
    "Woodpecker/Support/Redirect" => "/Support/Redirect.php",
    "Woodpecker/Support/Response" => "/Support/Response.php",
    "Woodpecker/Support/Request" => "/Support/Request.php",
    "Woodpecker/Controller" => "/Controller/Controller.php"
];

spl_autoload_register(function($class) use ($namespaces) {
  $class = str_replace('\\', '/', trim($class));
    if (isset($namespaces[$class]) !== false) {
        $file =  __DIR__ . $namespaces[$class];
    }
    elseif (strpos($class, "WPException") !== false) {
      $file =  __DIR__ . "/WPException.php";
    }
    elseif(strpos($class, "Woodpecker/Controllers/") !== false) {
      $className = $class;
      $className = str_replace('Woodpecker/Controllers/', '', $className);
      $kk = str_replace('/src', '', __DIR__);
      $file = $kk . "/Controllers/". $className . '.php';
    }else{
      error_log("error: $class");
    }
    
    if(file_exists($file)){
      require_once($file);    
    }else{
      error_log("not found $file");
    }
});
