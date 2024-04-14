<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

/**
 * GetMapping attribute used for routing.
 * It is permitted methods.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping
{
    /**
     * The constructor of the class.
     * It has two parameters - $path and $prefix.
     *
     * @param string $path The path where to map this GET request.
     * @param string $prefix The prefix to be used in the request URL. By default, it's set to 'api'.
     */
    public function __construct(
        public string $path,
        public string $prefix = 'api'
    )
    {
    }
}
