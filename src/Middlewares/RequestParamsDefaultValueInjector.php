<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Zenith\LaravelPlus\Attributes\Validators\Param;

class RequestParamsDefaultValueInjector
{
    /**
     * @throws ReflectionException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $controller = $request->route()->getController();
        $action = $request->route()->getActionMethod();
        $reflectionMethod = new ReflectionMethod($controller, $action);
        $defaultValues = collect($reflectionMethod->getAttributes(Param::class))
            ->flatMap(function (ReflectionAttribute $attribute) {
                $instance = $attribute->newInstance();

                return [
                    $instance->key => $instance->default,
                ];
            })->filter(fn ($value) => ! is_null($value))->all();
        $this->handleDefaultValues($request, $defaultValues);

        return $next($request);
    }

    private function handleDefaultValues(Request $request, array $defaultValues): void
    {
        $params = $request->all();
        foreach ($defaultValues as $keyPath => $defaultValue) {
            $keys = explode('.', $keyPath);

            $this->setDefault($params, $keys, $defaultValue);
        }
        foreach ($params as $key => $param) {
            $request->request->set($key, $param);
        }
    }

    private function setDefault(array &$params, array $keys, mixed $defaultValue): void
    {
        $key = array_shift($keys);
        if ($key === '*') {
            foreach ($params as &$item) {
                $this->setDefault($item, $keys, $defaultValue);
            }
        } else {
            if (count($keys) == 0) {
                if (! isset($params[$key])) {
                    $params[$key] = $defaultValue;
                }
            } else {
                if (! isset($params[$key]) || ! is_array($params[$key])) {
                    $params[$key] = [];
                }
                $this->setDefault($params[$key], $keys, $defaultValue);
            }
        }
    }
}
