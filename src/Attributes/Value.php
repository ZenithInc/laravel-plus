<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Value
{
   
    public function __construct(public string $pattern, public $defaultValue = null)
    {
    }
}
