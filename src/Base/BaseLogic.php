<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Base;

use Illuminate\Contracts\Container\BindingResolutionException;
use ReflectionAttribute;
use ReflectionClass;
use Zenith\LaravelPlus\Attributes\Autowired;

class BaseLogic
{
    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $reflectionClazz = new ReflectionClass($this);
        foreach ($reflectionClazz->getProperties() as $property) {
            $autowired = $property->getAttributes(Autowired::class);
            if (!$autowired) {
                continue;
            }
            /** @var ReflectionAttribute $autowired */
            $clazz = ($autowired[0])->newInstance()->value;
            $property->setValue($this, app()->make($clazz));
        }
    }
}
