<?php
declare(strict_types=1);

use Zenith\LaravelPlus\Bean;

class BookBean extends Bean
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

}
