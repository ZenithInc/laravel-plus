<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Alias
{
    public function __construct(
        public string $value
    ) {
    }
}
