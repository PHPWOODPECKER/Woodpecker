<?php
use Woodpecker\Controllers\Test;

$router->url('/test', [
     "class" => Controllers\Test::class, 
     "method" => "hello"
 ]);
