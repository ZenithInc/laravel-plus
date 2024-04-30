<?php
declare(strict_types=1);

use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Bean;

class SampleBean2 extends Bean
{

    protected string $username;

    #[BeanList(SampleBean::class)]
    protected array $subs;
}
