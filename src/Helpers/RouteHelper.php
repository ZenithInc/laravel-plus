<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

use Illuminate\Support\Facades\Route;
use ReflectionAttribute;
use ReflectionMethod;
use Zenith\LaravelPlus\Attributes\Routes\DeleteMapping;
use Zenith\LaravelPlus\Attributes\Routes\GetMapping;
use Zenith\LaravelPlus\Attributes\Routes\PostMapping;
use Zenith\LaravelPlus\Attributes\Routes\PutMapping;
use Zenith\LaravelPlus\Middlewares\RequestBodyInjector;

class RouteHelper
{

    /**
     * This function handles prefix notation for controllers. If the prefix does not start
     * with a '/', it appends it. It takes an instance of ReflectionAttribute which is
     * an attribute instance of the controller's prefix. It returns the prefix string
     * with a leading '/'.
     *
     * @param  ReflectionAttribute  $prefixAttribute  - The prefix attribute instance of the route.
     * @return string - The formatted prefix string.
     */
    public static function handleControllerPrefix(ReflectionAttribute $prefixAttribute): string
    {
        $prefix = ($prefixAttribute->newInstance())->value;

        return str_starts_with($prefix, '/') ? $prefix : '/'.$prefix;
    }

    /**
     * Iterate over provided methods and map associated routes attributes to routes.
     *
     * @param  array  $methods  Array of methods
     * @param  string  $prefix  Prefix for the route
     * @param  string  $controller  Controller responsible for the route handling
     */
    public static function handleMethodsAttributes(array $methods, string $prefix, string $controller): void
    {
        $routesMapping = [
            GetMapping::class => 'get',
            PostMapping::class => 'post',
            PutMapping::class => 'put',
            DeleteMapping::class => 'delete',
        ];
        foreach ($methods as $method) {
            $attributes = collect($method->getAttributes())->filter(function (ReflectionAttribute $attribute) use ($routesMapping) {
                return array_key_exists($attribute->getName(), $routesMapping);
            });
            if ($attributes->isEmpty()) {
                continue;
            }
            self::mapAttributesToRoutes($attributes->first(), $prefix, $controller, $method, $routesMapping);
        }
    }


    /**
     * Maps attribute to routes by creating a new instance of attribute, formulating the uri
     * and mapping the corresponding route method in Laravel's Route facade.
     *
     * @param  ReflectionAttribute  $attribute  Instance of PHP's ReflectionAttribute class identifying a route attribute
     * @param  string  $prefix  Prefix for the route uri
     * @param  string  $controller  Name of the controller handling the route
     * @param  ReflectionMethod  $method  Instance of PHP's ReflectionMethod representing a method in the controller
     * @param  array  $routesMapping  An associative array having  class references as keys and their corresponding routings as values
     */
    private static function mapAttributesToRoutes(ReflectionAttribute $attribute, string $prefix, string $controller, ReflectionMethod $method, array $routesMapping): void
    {
        $instance = $attribute->newInstance();
        $uri = $prefix.$instance->path;
        Route::{$routesMapping[$attribute->getName()]}($uri, [$controller, $method->getName()])
            ->middleware([RequestBodyInjector::class]);
    }
}
