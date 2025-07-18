<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Zenith\LaravelPlus\Attributes\Autowired;
use Zenith\LaravelPlus\Attributes\Value;

trait Injectable {
    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $reflectionClazz = new ReflectionClass($this);
        foreach ($reflectionClazz->getProperties() as $property) {
            $autowired = $property->getAttributes(Autowired::class);
            if ($autowired) {
                $property->setValue($this, app()->make($property->getType()->getName()));
            }
            $values = $property->getAttributes(Value::class);
            if (isset($values[0])) {
                $instance = $values[0]->newInstance();
                $pattern = $instance->pattern;
                $defaultValue = $instance->defaultValue;
                $property->setValue($this, Config::get($pattern, $defaultValue));
            }
        }
    }
}
