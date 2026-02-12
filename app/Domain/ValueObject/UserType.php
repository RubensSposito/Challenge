<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum UserType: string
{
    case COMMON = 'common';
    case MERCHANT = 'merchant';

    public function podeEnviarDinheiro(): bool
    {
        return $this === self::COMMON;
    }
}
