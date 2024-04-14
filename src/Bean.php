<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Override;
use ReflectionException;
use ReflectionProperty;
use ReturnTypeWillChange;

/**
 * Class Bean
 * Implements Arrayable and ArrayAccess interfaces
 */
class Bean implements Arrayable, ArrayAccess
{
    /**
     * @var array
     *            Holds raw data
     */
    protected array $_RAW = [];

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
        // Set default property and value.
        foreach ($this as $key => $value) {
            $key != '_RAW' && $this->_RAW[$key] = $value;
        }
        foreach ($data as $key => $value) {
            if (! property_exists($this, $key)) {
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
            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
     * Set offset value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->_RAW[] = $value;
        } else {
            $this->_RAW[$offset] = $value;
        }
    }

    /**
     * Check if offset exists
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->_RAW[$offset]);
    }

    /**
     * Unset offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->_RAW[$offset]);
    }

    /**
     * Get offset value
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->_RAW[$offset] ?? null;
    }

    /**
     * Convert the Bean to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->_RAW);
    }
}
