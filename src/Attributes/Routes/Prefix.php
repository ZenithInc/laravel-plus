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
     * @param string $path The path to be prefixed.
     * @param string $module The module name.
     */
    public function __construct(
        public string $path,
        public string $module = 'default',
    )
    {
    }
}