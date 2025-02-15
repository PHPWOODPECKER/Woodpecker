<?php
namespace Woodpecker;

use Woodpecker\Support\Response;
use Woodpecker\Support\Redirect;

class Controller {
  /**
   * @return Response class
   */
   protected function response(): Response
   {
     return new Response();
   }
     /**
   * @return Redirect class
   */
   protected function redirect(): Redirect
   {
     return new Redirect();
   }
}