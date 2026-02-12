<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface UserRepository
{
    public function exists(int $userId): bool;
    public function isMerchant(int $userId): bool;
}