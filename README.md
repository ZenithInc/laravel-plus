<p align="center">
  <a href="https://github.com/ZenithInc/laravel-plus">
   <img alt="Laravel-Plus-Logo" src="https://cdn-fusion.imgimg.cc/i/2024/c0a1618fd2b01412.png">
  </a>
</p>

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)
![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D11.0-red.svg)
[![Setup Automated](https://img.shields.io/badge/setup-automated-blue?logo=gitpod)](https://packagist.org)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## Laravel Plus

Laravel is an elegant framework that greatly simplifies development. However, no framework is truly "out-of-the-box" ready for all use cases; customization based on individual habits and project requirements is often necessary.

Laravel Plus addresses this need by incorporating AOP concepts from Java's Spring Boot and extensively utilizing PHP 8 attributes to streamline the development process.

> This project is currently under development. Please be cautious when using it in a production environment.

## Installation

This is installable via [Composer](https://getcomposer.org/) as [https://packagist.org/packages/zenithsu/laravel-plus](zenithsu/laravel-plus).

```shell
composer require zenithsu/laravel-plus
```

## Easy Router

In Laravel, routes need to be configured separately in `web.php` or `api.php`, which is not convenient during development as it requires switching between different files.

In contrast, frameworks like Spring Boot or Flask allow route configuration using annotations, making the coding process more fluid. Therefore, I have encapsulated annotation-based routing.

First, you need to register in api.php, the code is as follows:
```php
use Zenith\LaravelPlus\EasyRouter;

EasyRouter::register();
```
Then, you can use route annotations before controller methods:
```php
class UserController
{
    #[GetMapping(path: '/login')] 
    public function login() {}
}
```
You can access this API via `/api/login`. In addition to `GetMapping`, `PostMapping`, `PutMapping`, and `DeleteMapping` are also supported.

Furthermore, you can add a Prefix annotation to the controller to uniformly add a route prefix for all methods within the controller.
```php
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Routes\PostMapping;
use Zenith\LaravelPlus\Attributes\Routes\Prefix;

#[Prefix(path: '/user')]
class UserController
{
    #[GetMapping(path: '/login')]
    public function login() {}
    
    #[PostMapping(path: '/register')]
    public function register() {}
}
```

## Request

In Laravel-Plus, inspired by SpringBoot's RequestBody annotation, you can use a class to carry parameters from the body:

```php
use Zenith\LaravelPlus\Middlewares\RequestBodyInjector;

abstract class Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
           RequestBodyInjector::class, 
        ];
    }
}
```

Then, you can use the RequestBody annotation to inject parameters from the body:

```php
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Requests\RequestBody;

class UserController extends Controller
{
    #[GetMapping(path: '/login')]
    public function login(#[RequestBody] RegisterParams $params) {}
}

class RegisterParams
{
    // The modifiers must be public or protected.
    protected string $username;
    
    protected string $password;
}
```



## Validators

In Laravel, parameter validation is not a difficult task. However, it can be made even simpler through the use of annotations.

First, you need to enable the parameter validation middleware:
```php
use Zenith\LaravelPlus\Middlewares\RequestParamsDefaultValueInjector;

abstract class Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            RequestParamsDefaultValueInjector::class
            ParameterValidation::class,
        ];
    }
}
```

Then, you can use the Param annotation to validate parameters:

```php
use Zenith\LaravelPlus\Attributes\Validators\Param;

class UserController extends Controller
{
    #[GetMapping(path: '/login')]
    #[Param(key: 'username', rules: 'required|string|max:32', message: 'Username is required.')]
    public function login() {}
}
```
The `rule` supports Laravel's built-in rules, except for regular expressions.

For particularly complex rules, it is recommended to use custom validators:
```php
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isPass = strlen($value) >= 8 && preg_match('/[a-zA-Z]/', $value) &&
            preg_match('/\d/', $value) &&
            preg_match('/[^a-zA-Z\d]/', $value);
        if (! $isPass) {
            $fail('The :attribute must be least 8 characters and contain at least one letter, one number and one special character.');
        }
    }
}
```
In the example above, I wrote a custom rule for a common password validation:
```php
use Zenith\LaravelPlus\Attributes\Validators\Param;

class UserController
{
    #[GetMapping(path: '/login')]
    #[Param(key: 'username', rules: PasswordRule::class, message: 'password is error')]
    public function login() {}
}
```

By default, all parameters are required. You can use the `required` parameter to set them as optional, and use the `default` parameter to set default values:
```php
use Zenith\LaravelPlus\Attributes\Validators\Param;
use Zenith\LaravelPlus\Attributes\Requests\RequestBody;

class UserController extends Controller
{

    #[Param(key: 'page', rule: 'integer|min:1|max:100', default: 1, required: false, message: 'page is error')]
    public function queryList(#[RequestBody] QueryParams $params) 
    {
        dump($params->getPage());       // output: 1
    }
}
```


## Bean

Long-term dependency, PHPers are accustomed to using powerful arrays as carriers for all data. This is not an elegant practice and has the following problems:

* Array keys are easily misspelled, and when these errors are discovered, it's already at runtime.
* The coding process is not smooth; you always need to pause to think about what the next key is.
* It violates the single responsibility principle, often having all data in one huge array.
* It reduces code extensibility, readability, and robustness...

Therefore, I introduced the concept of Bean. A Bean is a data carrier with strongly typed properties, allowing you to get better hints during the coding process:

```php
use Zenith\LaravelPlus\Bean;

/**
 * @method getUsername()
 * @method setUsername()
 * @method getPassword()
 * @method setPassword()
 */
class RegisterParams extends Bean
{
    protected string $username;
    
    protected string $password;
}

new RegisterParams(['username' => 'bob', 'password' => 'passw0rd']);
```
You can initialize a Bean using an array, which is the most common method.Of course, sometimes you can also convert from one Bean to another Bean, and it will filter out mismatched fields:
```php
use Zenith\LaravelPlus\Bean;

$bean = new Bean();
class Bar extends Bean {  
    // some properties  
}
Bar::fromBean($bean)
```
You can easily convert a Bean to an array or JSON, By default, snake case naming will be used. You can turn off this feature using the usingSnakeCase parameter:
```php
use Zenith\LaravelPlus\Bean;

$bean = new Bean();
$arr = $bean->toArray(usingSnakeCase: false)
$json = $bean->toJson(usingSnakeCase: true);
```

Sometimes, you may need to compare two Beans:
```php
use Zenith\LaravelPlus\Bean;
(new Bean())->equals($bean2);
```

Often, we need to perform preliminary work such as type conversion on the data passed from the client:
```php
use Zenith\LaravelPlus\Bean::
use Zenith\LaravelPlus\Attributes\TypeConverter;

class User extends Bean
{
    
    #[TypeConverter(value: BoolConverter::class)]
    protected BoolEnum $isGuest;
}

class BoolConverter
{
    public function convert(bool|string $value): BoolEnum
    {
        if ($value === 'YES' || $value === 'yes' || $value === 'y' || $value === 'Y') {
            return BoolEnum::TRUE;
        }
        if ($value === 'NO' || $value === 'no' || $value === 'N' || $value === 'n') {
            return BoolEnum::FALSE;
        }

        return $value ? BoolEnum::TRUE : BoolEnum::FALSE;
    }
}
```
You can even perform XSS filtering.

A particularly useful feature of Beans is their support for nesting:
```php
use Zenith\LaravelPlus\Bean;

class User extends Bean
{
    protected Company $company;
}

class Company extends Bean
{
    protected string $name;
}
```
It even supports array nesting:
```php
use Zenith\LaravelPlus\Bean;
use Zenith\LaravelPlus\Attributes\BeanList;

/**
 * @method Company[] getCompanies()
 */
class User extends Bean
{
    /**
     * @var Company[]
     */
    #[BeanList(value: Company::class)]
    protected array $companies;
}

$user = new User(['companies' => [['name' => 'Zenith'], ['name' => 'Google']]]);
foreach ($user->getCompanies() as $company) {
    dump($company->getName());
}
```

### Autowired

In the Java Spring Boot framework, the `@Autowired` annotation is used to automatically inject dependencies. In Laravel-Plus, we can use the `#[Autowired]` annotation to achieve the same effect。

```php
use Zenith\LaravelPlus\Traits\Injectable;

class UserController
{
    use Injectable;
  
    #[Autowired]
    private UserService $userService;
    
    public function register()
    {
        $this->userService->register(); 
    }
}
```