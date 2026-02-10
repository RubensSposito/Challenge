<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

final class Transfer
{
    public function __construct(
        public readonly int $id,
        public readonly int $payerId,
        public readonly int $payeeId,
        public readonly Money $valor,
        public readonly string $status,
        public readonly string $criadoEm
    ) {}
}
