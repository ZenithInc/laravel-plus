<?php

use Zenith\LaravelPlus\Attributes\UnionType;
use Zenith\LaravelPlus\Bean;

class TextSettings extends Bean
{

    protected int $minLength;

    protected int $maxLength;
}

class NumberSettings extends Bean
{
    protected int $minValue;

    protected int $maxValue;
}

class Component extends Bean
{

    protected string $type;

    #[UnionType(type: 'type', map: [
        'text' => TextSettings::class,
        'number' => NumberSettings::class
    ])]
    protected TextSettings | NumberSettings $settings;
}
