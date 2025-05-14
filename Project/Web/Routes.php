<?php
use Woodpecker\Controllers\User;
use Woodpecker\DataBase\Table;

// view('native');
// $router->url('/home/', function(){
//   view('home');
// });
// $router->url('/about/', function(){
//   view('about');
// });
// $router->url('/contact/', function(){
//   view('contact');
// });
// $router->url('/portfolio/', function(){
//   view('portfolio');
// });


$router->url(['url' => '/test', 'rate' => 10], function() use ($router){
  $router->get(['name', 'age'], function($name, $age){
    Table::init();
    Table::save('users', [
      'name' => $name,
      'age' => $age
      ]);
  });
});

