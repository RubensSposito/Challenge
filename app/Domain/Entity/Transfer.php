<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

final class Transfer implements \JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly int $payerId,
        public readonly int $payeeId,
        public readonly Money $valor,
        public readonly string $status,
        public readonly string $criadoEm
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'payer' => $this->payerId,
            'payee' => $this->payeeId,
            'value' => $this->valor->toDecimal(),   
            'status' => $this->status,
            'createdAt' => $this->criadoEm,
        ];
    }
}