<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface NotifierGateway
{
    public function notificar(int $userId, string $mensagem): bool;
}
