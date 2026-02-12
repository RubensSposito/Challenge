<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface AuthorizerGateway
{
    public function autorizar(): bool;
}
