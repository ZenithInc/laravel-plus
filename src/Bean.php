<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReturnTypeWillChange;
use Zenith\LaravelPlus\Attributes\Alias;

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
            $alias = $this->getAlias($reflectionProperty);
            if ($alias) {
                $this->_alias[$reflectionProperty->getName()] = $alias;
            }
        }
        foreach ($data as $key => $value) {
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
            $reflectProperty = new ReflectionProperty($this, $key);
            if (is_array($value) && is_subclass_of($reflectProperty->getType()->getName(), Bean::class)) {
                $bean = (new ($reflectProperty->getType()->getName()));
                $bean->init($value);
                $this->$key = $bean;
                $this->_RAW[$key] = $value;

                continue;
            }

            $this->$key = $value;
            $this->_RAW[$key] = $value;
        }

        return $this;
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
}
