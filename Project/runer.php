<?php

require_once(__DIR__ . '/Woodpecker/autoload.php');

use Woodpecker\Support\Request;

use Woodpecker\Router;
$router = new Router();
include(__DIR__ . "/Web/Routes.php");
$router->run();

  function view(string $patch, array $data = []): void
{
    $file = __DIR__ . '/Web/View/' . $patch . '.php';

    if (!file_exists($file)) {
        throw new WPException(" view : Error: View file '$patch' not found.");
        return;
    }

    if (!empty($data)) {
        extract($data);

        ob_start();
        include($file);
        $content = ob_get_clean();
        response()->setBody($content)->send();
    } else {
        include($file);
    }
}



  use Woodpecker\Support\Response;

  function response(): Response
  {
    return new Response();
  }


use Woodpecker\Support\Redirect;

  function redirect(): Redirect
  {
    return new Redirect();
  }