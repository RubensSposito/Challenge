<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Money;

final class Wallet
{
    public function __construct(
        public readonly int $userId,
        private string $saldoDecimal
    ) {}

    public function saldo(): Money
    {
        return Money::fromDecimalString($this->saldoDecimal);
    }

    public function debitar(Money $valor): void
    {
        $novoSaldo = (\bcsub($this->saldoDecimal, $valor->toDecimal(), 2));

        if (bccomp($novoSaldo, '0.00', 2) === -1) {
            throw new \RuntimeException('Saldo insuficiente.');
        }

        $this->saldoDecimal = $novoSaldo;
    }

    public function creditar(Money $valor): void
    {
        $this->saldoDecimal = (\bcadd($this->saldoDecimal, $valor->toDecimal(), 2));
    }
}
