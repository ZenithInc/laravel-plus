<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Base;

use Zenith\LaravelPlus\Bean;
use Zenith\LaravelPlus\Optional;

interface ICurlRepository
{

    public function findById(int $id): Optional;

    public function existsById(int $id): bool;

    public function create(Bean $bean): int;

    public function remove(int $id): void;

    public function modify(int $id, Bean $params): void;

}
