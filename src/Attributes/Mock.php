<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes;

use Attribute;
use Zenith\LaravelPlus\MockType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mock
{

    /**
     * Mock constructor.
     *
     * @param string $value The value to be used for mocking
     * @param string $comment The comment to be used for mocking
     * @param MockType $type The type of the value to be used for mocking
     */
    public function __construct(
        public string $value,
        public string $comment = '',
        public MockType $type = MockType::STRING,
    )
    {
    }
}
