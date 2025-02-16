# Woodpecker
**Woodpecker micro framework**

## installation

- **The only way to install and use this micro framework is to download it from here and upload it to your host**

-  **To use it, just add the `autoload` file to your project with `require_once`**

  ```php
  require_once(__DIR__. "/Woodpecker/src/autoload.php");
  ```

## Capabilities

### Router 
- [Complete content](src/Router/)
  
 #### using 
 ```php
use Woodpecker\Router;

$router = new Router();

$router->url('/patch', function() use ($router){
$router->get(['name', 'email'], "Woodpecker\Controllers\User@request:find");
});
```

 
### Database
- [Complete content](src/DataBase/)
  
 #### using 
 ##### Database
 ```php
use Woodpecker\DataBase\DataBase;

DataBase::connection([]);

DataBase::dropTable('user');
```
##### Table 
```php
use Woodpecker\DataBase\Table;

Table::connection([]);

echo Table::find('user', 'name', 'ali')->getJson();

```
### Validator

- [Complete content](src/Validator/)

  #### using
  ```php
  use Woodpecker\Validator;

  Validator::make([], []);

  if(Validator::fails()){
  }else{
  echo Validator::errors();
  }
  

  ```

### Crypto

- [Complete content](src/Crypto/)

  #### using 
  ```php
  use Woodpecker\Crypto;

  Crypto::encrypt('', '');
  ```


# Manufacturer information 
- `name` : **woodpecker**
- `telegram` : [Woodpeacker_dev](https://t.me/Woodpeacker_dev)
