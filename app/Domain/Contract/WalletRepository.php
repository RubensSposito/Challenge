<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface WalletRepository
{
    public function getBalanceForUpdate(int $userId): int; // cents
    public function updateBalance(int $userId, int $newBalanceCents): void;
}   