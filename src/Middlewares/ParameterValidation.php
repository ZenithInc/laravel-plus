<?php

namespace Zenith\LaravelPlus\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use Zenith\LaravelPlus\Attributes\Validators\Param;
use Zenith\LaravelPlus\Exceptions\ValidatedErrorException;

class ParameterValidation
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws ReflectionException
     * @throws ValidatedErrorException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $controller = $request->route()->getController();
        $action = $request->route()->getActionMethod();
        $reflectionMethod = new ReflectionMethod($controller, $action);
        $params = collect($reflectionMethod->getAttributes(Param::class))
            ->map(function (ReflectionAttribute $attribute) {
                $instance = $attribute->newInstance();
                return [
                    'key' => $instance->key,
                    'rule' => explode('|', $attribute->newInstance()->rule),
                    'message' => $instance->message,
                ];
            });
        $rules = $messages = [];
        foreach ($params as $param) {
            $rules[$param['key']] = $param['rule'];
            $messages[$param['key']] = $param['message'];
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->stopOnFirstFailure()->fails()) {
            throw new ValidatedErrorException($validator->errors()->first());
        }

        return $next($request);
    }
}
