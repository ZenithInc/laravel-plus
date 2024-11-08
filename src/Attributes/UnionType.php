<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UnionType
{

    public function __construct(
        public string $type,
        public array $map
    ) {}

}
