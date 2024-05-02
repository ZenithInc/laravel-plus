<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Middlewares;

use Zenith\LaravelPlus\Attributes\Requests\RequestBody;
use Zenith\LaravelPlus\Bean;
use Closure;
use Illuminate\Http\Request;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class that handles request body injection
 */
class RequestBodyInjector
{
    /**
     * Handle the incoming HTTP request.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in stack
     *
     * @throws ReflectionException If a class does not exist or fails to be autoloaded
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        if ($route === null) {
            return $next($request);
        }
        $controller = $route->getController();
        $action = $route->getActionMethod();
        $reflectionMethod = new ReflectionMethod($controller, $action);
        $parameters = $reflectionMethod->getParameters();
        foreach ($parameters as $parameter) {
            $attributes = collect($parameter->getAttributes(RequestBody::class));
            if ($attributes->isEmpty()) {
                continue;
            }
            $this->addBodyToRequestIfBean($request, $parameter);
        }

        return $next($request);
    }

    /**
     * Method to add a body to the request, if the object is an instance of the Bean class.
     *
     * @param Request $request The incoming HTTP request
     * @param ReflectionParameter $parameter The parameter object from the PHP Reflection class.
     */
    private function addBodyToRequestIfBean(Request $request, ReflectionParameter $parameter): void
    {
        $params = $request->input();
        $bean = new ($parameter->getType()->getName())($params);
        if ($bean instanceof Bean) {
            app()->instance($parameter->getType()->getName(), $bean);
        }
    }
}