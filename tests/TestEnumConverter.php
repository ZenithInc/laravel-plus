<?php

declare(strict_types=1);

class TestEnumConverter
{

    public function convert(string $status): TestEnum
    {
        return TestEnum::from($status);
    }
}
