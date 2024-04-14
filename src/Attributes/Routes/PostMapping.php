<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

/**
 * PostMapping attribute used for routing.
 * It is permitted on methods.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PostMapping
{

    /**
     * PostMapping constructor.
     *
     * @param string $path The path where the POST request is mapped.
     * @param string $prefix The prefix used for the path, 'api' is used by default.
     */
    public function __construct(
        public string $path,
        public string $prefix = 'api'
    )
    {
    }
}
