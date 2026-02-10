<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum UserType: string
{
    case COMMON = 'common';
    case MERCHANT = 'merchant';

    public function podeEnviarDinheiro(): bool
    {
        // Aqui deixo explícito que somente usuários comuns podem pagar
        return $this === self::COMMON;
    }
}
