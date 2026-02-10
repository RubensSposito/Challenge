<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface AuthorizerGateway
{
    // Aqui eu consulto o serviço externo antes de efetivar a transferência
    public function autorizar(): bool;
}
