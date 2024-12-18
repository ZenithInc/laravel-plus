<?php

namespace Zenith\LaravelPlus\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Zenith\LaravelPlus\Attributes\Validators\Param;
use Zenith\LaravelPlus\Exceptions\ValidatedErrorException;

class ParameterValidation
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): mixed $next
     * @throws ReflectionException
     * @throws ValidatedErrorException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $controller = $request->route()->getController();
        $action = $request->route()->getActionMethod();
        $reflectionMethod = new ReflectionMethod($controller, $action);
        $params = collect($reflectionMethod->getAttributes(Param::class))
            ->map(function (ReflectionAttribute $attribute) {
                $instance = $attribute->newInstance();
                $rules = explode('|', $attribute->newInstance()->rules);
                foreach ($rules as &$rule) {
                    if (class_exists($rule)) {
                        $rule = new $rule();
                    }
                }
                $isContainRequiredRule = ! collect($rules)
                    ->filter(fn ($rule) => is_string($rule))
                    ->filter(fn ($rule) => str_contains($rule, 'required'))
                    ->isEmpty();
                if (! $isContainRequiredRule && $instance->required) {
                    $rules[] = 'required';
                }
                return [
                    'key' => $instance->key,
                    'rule' => $rules,
                    'message' => $instance->message,
                    'required' => $instance->required,
                ];
            });
        $rules = $keys = $messages = [];
        foreach ($params as $param) {
            $keys[] = $param['key'];
            $rules[$param['key']] = $param['rule'];
            // Multiple language support.
            if (str_starts_with($param['message'], 'trans:')) {
                $key = substr($param['message'], strlen('trans:'));
                $messages[$param['key']] = trans($key) ?? $param['message'];
            } else {
                $messages[$param['key']] = $param['message'];
            }
        }
        $routeParameters = $request->route()->parameters();
        $parameters = array_merge($routeParameters, $request->all());
        $validator = Validator::make($parameters, $rules, $messages);
        if ($validator->stopOnFirstFailure()->fails()) {
            throw new ValidatedErrorException($validator->errors()->first());
        }

        return $next($request);
    }
}