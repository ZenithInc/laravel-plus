<?php
declare(strict_types=1);

use Zenith\LaravelPlus\Attributes\Alias;
use Zenith\LaravelPlus\Attributes\BeanList;
use Zenith\LaravelPlus\Attributes\TypeConverter;
use Zenith\LaravelPlus\Bean;

class SampleBean extends Bean
{

    protected array $_skip = ['pivot'];

    protected string $username;

    #[Alias(value: 'latest_login_ip')]
    public string $latestLoginIp;

    /**
     * @var BookBean[]
     * Holds an array of books.
     */
    #[BeanList(value: BookBean::class)]
    public array $books = [];

    #[TypeConverter(value: 'intval')]
    protected int $page = 1;

    #[TypeConverter(value: TestEnumConverter::class)]
    protected TestEnum $status;

    public function getPage(): int
    {
        return $this->page;
    }

    public function getStatus(): TestEnum
    {
        return $this->status;
    }
}
