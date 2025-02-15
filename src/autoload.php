<?php
$namespaces = [
   "Woodpecker/WPException" => "/WPException.php",
    "Woodpecker/Crypto" => "/Crypto/Cryption.php",
    "Woodpecker/Filemanager" => "/FileManage/FileManager.php",
    "Woodpecker/DataBase/Table" => "/DataBase/Table.php",
    "Woodpecker/DataBase/Tools" => "/DataBase/Tools.php",
    "Woodpecker/Router" => "/Router/RouterSystem.php",
    "Woodpecker/Validator" => "/Validator/ValidatorSystem.php",
    "Woodpecker/Helper/Redirect" => "/HelperFunction/Redirect.php",
    "Woodpecker/Helper/Response" => "/HelperFunction/Response.php",
    "Woodpecker/Helper/Request" => "/HelperFunction/Request.php",
    "Woodpecker/Controller" => "/Controller/Controller.php"
];

// $helperNamespace = [
//   "/HelperFunction/Redirect.php",
//  "/HelperFunction/Response.php",
//  "/HelperFunction/Request.php",
//   ];
//   
//   foreach ($helperNamespace as $helper){
//     require_once(__DIR__ . $helper);
//   }

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
      error_log("Loading $class from " . $file);
      require_once($file);
    
    }else{
      error_log("not found $file");
    }
});
