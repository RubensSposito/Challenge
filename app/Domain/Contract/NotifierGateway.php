<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface NotifierGateway
{
    // Aqui eu tento notificar o recebedor, mas falha não invalida a transferência
    public function notificar(int $userId, string $mensagem): bool;
}
