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
     * Class constructor.
     *
     * @param string $key The key for the validation rule.
     * @param string $rule The validation rule to apply.
     * @param bool $required Determines if the field is required or not (default is true).
     * @param string $message The custom error message for the validation rule (default is self::DEFAULT_MESSAGE).
     */
    public function __construct(
        public string $key,
        public string $rule,
        public mixed $default = null,
        public bool $required = true,
        public string $message = self::DEFAULT_MESSAGE
    )
    {
    }
}
