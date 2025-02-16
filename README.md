# Woodpecker
Woodpecker micro framework

## Capabilities

### Router 
 #### using 
 ```php
use Woodpecker\Router;

$router = new Router();

$router->url('/patch', function() use ($router){
$router->get(['name', 'email'], "Woodpecker\Controllers\User@request:find");
});
```
- [Complete content](src/Router/)
### Database

### Validator

### Crypto
