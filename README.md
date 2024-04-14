# Laravel Plus

Laravel-Plus is an efficient Laravel extension package designed to enhance development productivity. It integrates powerful annotation handling and practical helper classes, making the development of Laravel applications more convenient and flexible.

> This project is currently under development. Please be cautious when using it in a production environment.

## Easy Router

We can use annotations to register routes. For example:
```php
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Routes\Prefix;

#[Prefix(value: '/api')]
class Controller
{
    #[GetMapping(path: '/greeting')]
    public function greeting() {}
}
```
Then, register the route in files such as `web.php` or `api.php`:
```php
use Zenith\LaravelPlus\EasyRouter;

EasyRouter::register();
```