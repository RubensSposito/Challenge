<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\DomainException;

final class Money
{
    private function __construct(
        private readonly string $valorDecimal
    ) {}

    public static function fromDecimalString(string $valor): self
    {
        // Aqui eu valido o formato do dinheiro recebido no V1 (decimal)
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
            throw new DomainException('Valor monetário inválido.');
        }

        if (\bccomp($valor, '0.00', 2) <= 0) {
            throw new DomainException('O valor da transferência deve ser maior que zero.');
        }

        return new self(number_format((float) $valor, 2, '.', ''));
    }

    // Gancho claro para V2 (centavos como inteiro)
    public static function fromCents(int $centavos): self
    {
        if ($centavos <= 0) {
            throw new DomainException('O valor da transferência deve ser maior que zero.');
        }

        return new self(\bcdiv((string) $centavos, '100', 2));
    }

    public function toDecimal(): string
    {
        return $this->valorDecimal;
    }

    public function maiorQue(Money $outro): bool
    {
        return (\bccomp($this->valorDecimal, $outro->valorDecimal, 2) === 1);
    }
}
