# Woodpecker
**Woodpecker micro framework**

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

### Crypto
