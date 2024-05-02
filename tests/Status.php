<?php

use Zenith\LaravelPlus\Attributes\TypeConverter;
use Zenith\LaravelPlus\Bean;

class Status extends Bean
{

    #[TypeConverter(value: TestEnumConverter::class)]
    protected TestEnum $value;

    #[TypeConverter(value: 'intval')]
    protected int $page;
}
