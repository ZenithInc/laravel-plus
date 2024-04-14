<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

/**
 * PutMapping attribute used for routing.
 * It is permitted methods.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PutMapping
{

    public function __construct(
        public string $path,
        public string $prefix = 'api'
    )
    {
    }
}
