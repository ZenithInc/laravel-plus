<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ParameterValidator
{
    public function __construct(
        public string $class
    ) {
    }
}
