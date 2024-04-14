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

## Easy Docs

Currently, we support using VitePress to build API documentation. You simply need to create a VitePress-based project in the root directory of your project and name it docs. For how to create the project, you can refer to its official documentation.

Then, you just need to register the documentation-related commands in the project, by editing App\Providers\AppService\Provider.php, as follows:
```php
use Zenith\LaravelPlus\Commands\DocsRun;
use Zenith\LaravelPlus\Commands\DocsBuild;

public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            DocsBuild::class,
            DocsRun::class,            
        ]);
    }
}
```
你可以基于我们提供的注解来生成接口文档:
```shell
php artisan docs:build
php artisan docs:run
```