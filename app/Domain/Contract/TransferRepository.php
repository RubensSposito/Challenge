<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface TransferRepository
{
    public function create(int $payerId, int $payeeId, int $amountCents): int;
}