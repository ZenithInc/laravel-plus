<?php
declare(strict_types=1);

namespace Zenith\LaravelPlus\Attributes\Validators;

use Attribute;

/**
 * Attribute that targets methods.
 * Can be used to set params on methods.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Param
{

    public const string DEFAULT_MESSAGE = 'The :attribute field is error';

    /**
     * Param constructor.
     * Constructs a Param object.
     *
     * @param string $key Parameter key for the method.
     * @param string $rule The rule under which the key must comply.
     * @param string $message The message attached to the key if it fails to comply with the rule.
     *
     */
    public function __construct(
        public string $key,
        public string $rule,
        public string $message = self::DEFAULT_MESSAGE
    )
    {
    }
}
