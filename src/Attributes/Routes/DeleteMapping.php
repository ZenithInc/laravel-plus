<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Routes;

use Attribute;

/**
 * DeleteMapping attribute used for routing.
 * It is permitted on methods.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeleteMapping
{

    /**
     * DeleteMapping constructor. Represents a delete endpoint in the api, mostly used for resource deletion
     *
     * @param string $path Defines the path for the delete route
     * @param string $prefix Defines the prefix for the route, by default set to 'api'
     */
    public function __construct(
        public string $path,
        public string $prefix = 'api'
    )
    {
    }
}
