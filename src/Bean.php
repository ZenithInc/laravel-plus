<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionProperty;
use Throwable;
use Zenith\LaravelPlus\Attributes\Alias;
use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Attributes\Mock;
use Zenith\LaravelPlus\Attributes\TypeConverter;
use Zenith\LaravelPlus\Exceptions\PropertyNotFoundException;

/**
 * Class Bean
 * Implements Array able and ArrayAccess interfaces
 */
class Bean implements Arrayable
{

    protected array $_skip = [];

    private array $_meta = [];

    /**
     * @param array $data
     * @throws ReflectionException
     */
    public function __construct(array $data = [])
    {
        $this->collectMetaInfo();
        $this->init($data);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function collectMetaInfo(): void
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            if (str_starts_with($property->getName(), '_') || in_array($property->getName(), $this->_skip)) {
                continue;
            }
            $attributes = $property->getAttributes();
            $alias = $converter = $beanList = null;
            $mock = [];
            foreach ($attributes as $attribute) {
                $enums = [];
                if ($attribute->getName() === Alias::class) {
                    $alias = $attribute->newInstance()->value;
                }
                if ($attribute->getName() === TypeConverter::class) {
                    $converter = $attribute->newInstance()->value;
                }
                if ($attribute->getName() === BeanList::class) {
                    $beanList = $attribute->newInstance()->value;
                }
                if ($attribute->getName() === Mock::class) {
                    $mockInstance = $attribute->newInstance();
                    if ($mockInstance->type === MockType::OBJECT || $mockInstance->type === MockType::OBJECT_ARRAY) {
                        $mockValue = (new $mockInstance->value([]))->getMockData();
                    } else if ($mockInstance->type === MockType::ENUM) {
                        $mockValue = $mockInstance->value;
                        $enums = $this->getEnumInfo($mockInstance->value);
                    } else {
                        $mockValue = $mockInstance->value;
                    }
                    $mock = [
                        'value' => $mockValue,
                        'comment' => $mockInstance->comment,
                        'type' => strtolower($mockInstance->type->name),
                        'enums' => $enums,
                    ];
                }
            }
            $snake = $alias !== null ? Str::snake($alias) : Str::snake($property->getName());

            $this->_meta[$property->getName()] = [
                'type' => $property->getType()?->getName(),
                'alias' => $alias,
                'snake' => $snake,
                'reflectProperty' => $property,
                'converter' => $converter,
                'beanList' => $beanList,
                'value' => null,
                'mock' => $mock,
            ];
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getEnumInfo(string $enumClazz): array
    {
        $reflectionEnum = new ReflectionClass($enumClazz);
        if (!$reflectionEnum->isEnum()) {
            return [];
        }
        $enums = [];
        $constants = $reflectionEnum->getConstants();
        foreach ($constants as $name => $constant) {
            $reflectionConstant = new ReflectionClassConstant($enumClazz, $name);
            $alias = collect($reflectionConstant->getAttributes())->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === Alias::class)
                ->first();
            if ($alias === null) {
                $enums[$name] = '';
                continue;
            }
            $enums[$name] = $alias->newInstance()->value;
        }
        return $enums;
    }

    private function init(array $data): void
    {
        foreach ($this->_meta as $propertyName => $meta) {
            $value = $data[$meta['alias']] ?? $data[$propertyName] ?? $data[$meta['snake']] ?? null;
            if ($value === null) {
                continue;
            }
            if ($meta['converter'] !== null) {
                $value = $this->convertValue($meta['converter'], $value);
            }
            if ($meta['beanList'] !== null) {
                $value = $this->convertBeanList($meta['beanList'], $value);
            }
            if (is_subclass_of($meta['type'], Bean::class)) {
                $wrapper = $meta['type'];
                $value = new $wrapper($value);
            }
            $meta['reflectProperty']->setValue($this, $value);
            $this->_meta[$propertyName]['value'] = $value;
        }
    }


    private function convertValue(string $convertor, mixed $value): mixed
    {
        if (function_exists($convertor)) {
            return $convertor($value);
        }
        try {
            return (new $convertor())->convert($value);
        } catch (Throwable $exception) {
            return $value;
        }
    }

    private function convertBeanList(string $clazz, array $items): array
    {
        $beanList = [];
        foreach ($items as $item) {
            $beanList[] = new $clazz($item);
        }
        return $beanList;
    }

    /**
     * Convert the Bean to array
     *
     * @throws ReflectionException
     */
    #[Override]
    public function toArray(bool $usingSnakeCase = true): array
    {
        $arr = [];
        foreach ($this->_meta as $propertyName => $meta) {
            $value = $meta['value'];
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }
            if (is_array($value) && isset($value[0]) && is_subclass_of($value[0], Bean::class)) {
                $values = [];
                foreach ($value as $item) {
                    $values[] = $item->toArray();
                };
                $value = $values;
            }

            $key = $usingSnakeCase ? $meta['snake'] : $propertyName;
            $arr[$key] = $value;
        }

        return $arr;
    }

    /**
     * Convert the Bean to JSON
     * @throws ReflectionException
     */
    public function toJson(bool $usingSnakeCase = true): string
    {
        return json_encode($this->toArray($usingSnakeCase));
    }

    public function getMockData(): array
    {
        $properties = [];
        foreach ($this->_meta as $property => $meta) {
            $properties[$property] = $meta['mock'];
        }

        return $properties;
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, strlen('set')));
            if (property_exists($this, $property)) {
                $this->_meta[$property]['reflectProperty']->setValue($this, $arguments[0]);
                $this->_meta[$property]['value'] = $arguments[0];
                return $this;
            }
            throw new PropertyNotFoundException("The property '{$property}' does not exist.");
        }
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, strlen('get')));
            return $this->_meta[$property]['value'];
        }
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function equals(Bean $bean): bool
    {
        $firstBean = $bean->toArray();
        $secondBean = $this->toArray();
        foreach ($firstBean as $key => $value) {
            if ($value !== $secondBean[$key]) {
                return false;
            }
        }

        return true;
    }
}