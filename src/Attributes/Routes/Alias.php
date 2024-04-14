<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Alias
{
    public function __construct(
        public string $value
    ) {
    }
}
