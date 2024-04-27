<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

use Illuminate\Contracts\Support\Arrayable;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Throwable;
use Zenith\LaravelPlus\Attributes\Alias;
use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Attributes\TypeConverter;

/**
 * Class Bean
 * Implements Arrayable and ArrayAccess interfaces
 */
class Bean implements Arrayable
{
    /**
     * @var array
     * Holds raw data
     */
    private array $_RAW = [];

    /**
     * @var array
     */
    private array $_alias = [];

    /**
     * @var array
     */
    protected array $_skip = [];

    /**
     * @throws ReflectionException
     */
    public function __construct(array $data = [])
    {
        $this->init($data);
    }

    /**
     * Initialize the Bean
     *
     * @throws ReflectionException
     */
    public function init(array $data): self
    {
        // initializes alias
        $reflectionClass = new ReflectionClass($this);
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            if (in_array($reflectionProperty->getName(), $this->_skip, true)) {
                continue;
            }
            $alias = $this->getAlias($reflectionProperty);
            if ($alias) {
                $this->_alias[$reflectionProperty->getName()] = $alias;
            }
        }
        foreach ($data as $key => $value) {
            if (in_array($key, $this->_skip, true)) {
                continue;
            }
            // Check alias
            foreach ($this->_alias as $k => $v) {
                if ($v === $key) {
                    $key = $k;
                }
            }
            if (!property_exists($this, $key)) {
                continue;
            }
            // Check field type, if type is bean, init it.
            $reflectionProperty = new ReflectionProperty($this, $key);
            $value = $this->covertValueType($reflectionProperty, $value);
            if (is_array($value) && is_subclass_of($reflectionProperty->getType()->getName(), Bean::class)) {
                $bean = (new ($reflectionProperty->getType()->getName()));
                $bean->init($value);
                $this->$key = $bean;
                $this->_RAW[$key] = $bean;
                continue;
            }
            if (is_array($value) && $this->initBeanList($reflectionProperty, $value)) {
                $this->$key = $reflectionProperty->getValue($this);
                $this->_RAW[$key] = $this->$key;
                continue;
            }
            $this->_RAW[$key] = $this->$key = $value;
        }
        return $this;
    }

    private function covertValueType(ReflectionProperty $reflectionProperty, mixed $value): mixed
    {
        $attributes = $reflectionProperty->getAttributes(TypeConverter::class);
        if (!$attributes) {
            return $value;
        }
        $functionOrClass = $attributes[0]->newInstance()->value;
        if (function_exists($functionOrClass)) {
            return $functionOrClass($value);
        }
        try {
            return (new $functionOrClass())->convert($value);
        } catch (Throwable $exception) {
            return $value;
        }
    }

    private function initBeanList(ReflectionProperty $reflectProperty, mixed $items): bool
    {
        $attributes = $reflectProperty->getAttributes(BeanList::class);
        if (!$attributes) {
            return false;
        }
        $clazz = $attributes[0]->newInstance()->value;
        $beanList = [];
        foreach ($items as $item) {
            $beanList[] = new $clazz($item);
        }
        $reflectProperty->setValue($this, $beanList);
        return true;
    }

    private function getAlias(ReflectionProperty $reflectionProperty): ?string
    {
        $attributes = collect($reflectionProperty->getAttributes());
        $aliasAttribute = $attributes->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Alias::class)->first();
        /** @var ReflectionAttribute $aliasAttribute */
        return $aliasAttribute?->newInstance()->value;
    }

    /**
     * Convert the Bean to array
     *
     * @throws ReflectionException
     */
    #[Override]
    public function toArray(): array
    {
        $arr = [];
        foreach ($this->_RAW as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }
            if (is_array($value) && is_subclass_of($value[0], Bean::class)) {
                $values = [];
                foreach ($value as $item) {
                    $values[] = $item->toArray();
                };
                $value = $values;
            }
            $arr[$this->_alias[$key] ?? $key] = $value;
        }

        return $arr;
    }

    /**
     * Convert the Bean to JSON
     * @throws ReflectionException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Sets the value of a dynamic property.
     *
     * @param string $name The name of the property to set.
     * @param mixed $value The value to set for the property.
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        if (in_array($name, $this->_skip, true)) {
            return;
        }
        $this->$name = $value;
        $this->_RAW[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->_RAW[$name];
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, strlen('set')));
            property_exists($this, $property) && $this->$property = $arguments[0];
            $this->_RAW[$property] = $this->$property;
            return $this;
        }
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, strlen('get')));
            return $this->_RAW[$property];
        }
        return $this;
    }
}