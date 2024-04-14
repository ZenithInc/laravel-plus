<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

/**
 * Prefix attribute used for routing.
 * It is permitted on methods.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
    /**
     * Prefix constructor.
     *
     * @param string $value The value to construct the Prefix with
     */
    public function __construct(
        public string $value
    )
    {
    }
}
