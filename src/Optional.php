<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus;

use UnexpectedValueException;
use Zenith\LaravelPlus\Exceptions\NotSuchElementException;

class Optional
{
    private mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @throws NotSuchElementException
     */
    public function get(): mixed
    {
        if ($this->value === null) {
            throw new NotSuchElementException();
        }

        return $this->value;
    }

    public static function of(mixed $value): self
    {
        if ($value === null) {
            throw new UnexpectedValueException();
        }

        return new self($value);
    }

    public static function ofNullable(mixed $value): self
    {
        return new self($value);
    }

    public function ifPresent(callable $func): void
    {
        if ($this->value !== null) {
            $func($this->value);
        }
    }

    public function isPresent(): bool
    {
        return is_null($this->value);
    }

    public function ofElseThrow(callable $exception): self
    {
        if ($this->value !== null) {
            return $this->value;
        }
        if (is_string($exception)) {
            throw $exception();
        }
        return $this;
    }
}
