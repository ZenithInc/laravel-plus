<?php
declare(strict_types=1);

use Zenith\LaravelPlus\Attributes\Alias;
use Zenith\LaravelPlus\Bean;

class SampleBean extends Bean
{

    protected string $username;

    #[Alias(value: 'latest_login_ip')]
    public string $latestLoginIp;
}
