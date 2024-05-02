<?php

use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Bean;

class Wrapper extends Bean
{

    #[BeanList(value: Item::class)]
    protected array $items;
}
