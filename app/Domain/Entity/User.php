<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserType;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $nomeCompleto,
        public readonly string $email,
        public readonly string $documento,
        public readonly UserType $tipo
    ) {}
}
