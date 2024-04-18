<?php
declare(strict_types=1);

use Zenith\LaravelPlus\Attributes\Alias;
use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Bean;

class SampleBean extends Bean
{

    protected string $username;

    #[Alias(value: 'latest_login_ip')]
    public string $latestLoginIp;

    /**
     * @var BookBean[]
     * Holds an array of books.
     */
    #[BeanList(value: BookBean::class)]
    public array $books = [];
}
