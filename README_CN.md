<p align="center">
  <a href="https://github.com/ZenithInc/laravel-plus">
   <img alt="Laravel-Plus-Logo" src="https://cdn-fusion.imgimg.cc/i/2024/c0a1618fd2b01412.png">
  </a>
</p>

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)
![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D11.0-red.svg)
[![Setup Automated](https://img.shields.io/badge/setup-automated-blue?logo=gitpod)](https://packagist.org)
<a href="https://packagist.org/packages/zenithsu/laravel-plus"><img src="https://img.shields.io/packagist/dt/zenithsu/laravel-plus" alt="Download statistics"></a>
![License](https://img.shields.io/badge/license-MIT-green.svg)

## Laravel Plus

中文文档 | [English](README.md)

Laravel 是一个优雅的框架，极大地简化了开发工作。然而，没有任何框架能够真正做到对所有用例都"开箱即用"；基于个人习惯和项目需求进行定制往往是必要的。

Laravel Plus 正是为了满足这一需求而生，它融合了 Java Spring Boot 的 AOP 概念，并广泛利用 PHP8 注解来简化开发流程。

> 该项目目前正在开发中，在生产环境中使用时请谨慎。

## 安装

通过 [Composer](https://getcomposer.org/) 安装 [zenithsu/laravel-plus](https://packagist.org/packages/zenithsu/laravel-plus)。

```shell
composer require zenithsu/laravel-plus
```

## 简易路由

在 Laravel 中，路由需要在 `web.php` 或 `api.php` 中单独配置，这在开发过程中并不方便，因为需要在不同文件之间切换。

相比之下，像 Spring Boot 或 Flask 这样的框架允许使用注解配置路由，使编码过程更加流畅。因此，我封装了基于注解的路由功能。

首先，你需要在 `api.php` 中注册，代码如下：
```php
use Zenith\LaravelPlus\EasyRouter;

EasyRouter::register();
```

然后，你可以在控制器方法前使用路由注解：
```php
class UserController
{
    #[GetMapping(path: '/login')] 
    public function login() {}
}
```

你可以通过 `/api/login` 访问此 API。除了 `GetMapping` 之外，还支持 `PostMapping`、`PutMapping` 和 `DeleteMapping`。

此外，你可以在控制器上添加 Prefix 注解，为控制器内的所有方法统一添加路由前缀。

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

## 请求处理

在 Laravel-Plus 中，受 SpringBoot 的 RequestBody 注解启发，你可以使用一个类来承载来自请求体的参数：

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

然后，你可以使用 RequestBody 注解来注入来自请求体的参数：

```php
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Requests\RequestBody;
use Zenith\LaravelPlus\Bean;

class UserController extends Controller
{
    #[GetMapping(path: '/login')]
    public function login(#[RequestBody] RegisterParams $params) {}
}

// RegisterParams 类必须继承 Bean 类
class RegisterParams extends Bean
{
    // 修饰符必须是 public 或 protected
    protected string $username;
    
    protected string $password;
}
```

## 验证器

在 Laravel 中，参数验证并不是一项困难的任务。然而，通过使用注解可以让它变得更加简单。

首先，你需要启用参数验证中间件：
```php
use Zenith\LaravelPlus\Middlewares\RequestParamsDefaultValueInjector;

abstract class Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            RequestParamsDefaultValueInjector::class,
            ParameterValidation::class,
        ];
    }
}
```

然后，你可以使用 Param 注解来验证参数：

```php
use Zenith\LaravelPlus\Attributes\Validators\Param;

class UserController extends Controller
{
    #[GetMapping(path: '/login')]
    #[Param(key: 'username', rules: 'required|string|max:32', message: 'Username is required.')]
    public function login() {}
}
```

`rule` 支持 Laravel 的内置规则，除了正则表达式。

对于特别复杂的规则，建议使用自定义验证器：
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

在上面的示例中，我编写了一个常见密码验证的自定义规则：
```php
use Zenith\LaravelPlus\Attributes\Validators\Param;

class UserController
{
    #[GetMapping(path: '/login')]
    #[Param(key: 'username', rules: PasswordRule::class, message: 'password is error')]
    public function login() {}
}
```

默认情况下，所有参数都是必需的。你可以使用 `required` 参数将它们设置为可选，并使用 `default` 参数设置默认值：
```php
use Zenith\LaravelPlus\Attributes\Validators\Param;
use Zenith\LaravelPlus\Attributes\Requests\RequestBody;

class UserController extends Controller
{
    #[Param(key: 'page', rule: 'integer|min:1|max:100', default: 1, required: false, message: 'page is error')]
    public function queryList(#[RequestBody] QueryParams $params) 
    {
        dump($params->getPage());       // 输出: 1
    }
}
```

## Bean

长期以来，PHP 开发者习惯于使用功能强大的数组作为所有数据的载体。这不是一个优雅的做法，并且存在以下问题：

* 数组键容易拼写错误，当发现这些错误时，已经是运行时了
* 编码过程不够流畅；你总是需要暂停思考下一个键是什么
* 违反了单一职责原则，经常将所有数据放在一个巨大的数组中
* 降低了代码的可扩展性、可读性和健壮性...

因此，我引入了 Bean 的概念。Bean 是一个具有强类型属性的数据载体，让你在编码过程中获得更好的提示：

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

你可以使用数组初始化 Bean，这是最常见的方法。当然，有时你也可以从一个 Bean 转换为另一个 Bean，它会过滤掉不匹配的字段：
```php
use Zenith\LaravelPlus\Bean;

$bean = new Bean();
class Bar extends Bean {  
    // 一些属性  
}
Bar::fromBean($bean)
```

你可以轻松地将 Bean 转换为数组或 JSON。默认情况下，将使用蛇形命名法。你可以使用 `usingSnakeCase` 参数关闭此功能：
```php
use Zenith\LaravelPlus\Bean;

$bean = new Bean();
$arr = $bean->toArray(usingSnakeCase: false);
$json = $bean->toJson(usingSnakeCase: true);
```

有时，你可能需要比较两个 Bean：
```php
use Zenith\LaravelPlus\Bean;
(new Bean())->equals($bean2);
```

通常，我们需要对从客户端传递的数据进行类型转换等预处理工作：
```php
use Zenith\LaravelPlus\Bean;
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

你甚至可以执行 XSS 过滤。

Bean 的一个特别有用的功能是支持嵌套：
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

它甚至支持数组嵌套：
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

## 自动装配

在 Java Spring Boot 框架中，`@Autowired` 注解用于自动注入依赖。在 Laravel-Plus 中，我们可以使用 `#[Autowired]` 注解来实现相同的效果。

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

use Zenith\LaravelPlus\Attributes\Service;

#[Service]
class UserService
{
    public function register() {}
}
```

`#[Autowired]` 注解可以用于属性。`#[Service]` 注解用于将类标记为服务，这是自动装配所必需的。

## 配置值注入

除了依赖注入之外，Laravel-Plus 还支持配置值注入功能，让你可以直接将 Laravel 配置文件中的值注入到类属性中：

```php
use Zenith\LaravelPlus\Traits\Injectable;
use Zenith\LaravelPlus\Attributes\Value;

class DatabaseService
{
    use Injectable;
    
    #[Value('database.connections.mysql.host', 'localhost')]
    private string $dbHost;
    
    #[Value('database.connections.mysql.port', 3306)]
    private int $dbPort;
    
    #[Value('app.timezone', 'UTC')]
    private string $timezone;
    
    public function connect()
    {
        // 使用注入的配置值
        echo "Connecting to {$this->dbHost}:{$this->dbPort}";
    }
}
```

`#[Value]` 注解的第一个参数是配置键名，第二个参数是可选的默认值。如果配置不存在，将使用默认值。
