<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Zenith\LaravelPlus\Attributes\Routes\Prefix;
use Zenith\LaravelPlus\Helpers\ControllerHelper;
use Zenith\LaravelPlus\Helpers\RouteHelper;

class EasyRouter
{

    public static function register(): void
    {
        $controllerPath = app()->path('Http/Controllers');
        $controllerFiles = ControllerHelper::scanForFiles($controllerPath);

        // Scan attributes in controllers and register routes.
        foreach ($controllerFiles as $file) {
            try {
                $controller = ControllerHelper::convertPathToNamespace($file);
                $reflectionClass = new ReflectionClass($controller);
            } catch (ReflectionException $e) {
                dd($e->getMessage());
            }

            $controllerPrefixAttributes = collect($reflectionClass->getAttributes(Prefix::class));
            $prefix = $controllerPrefixAttributes->isEmpty() ? '' : RouteHelper::handleControllerPrefix($controllerPrefixAttributes->first());

            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            RouteHelper::handleMethodsAttributes($methods, $prefix, $controller);
        }
    }
}
