<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Base;

use Illuminate\Database\Eloquent\Collection;
use Zenith\LaravelPlus\Bean;
use Zenith\LaravelPlus\Optional;

interface ICurlRepository
{

    public function findById(int $id): Optional;

    public function existsById(int $id): bool;

    public function create(Bean $bean): int;

    public function remove(int $id): void;

    public function modify(int $id, Bean $params): void;

    public function findAll(): Collection;

    public function findByIds(array $ids): array;

    public function existsByFields(array $conditions): bool;
}
