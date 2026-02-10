<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Domain\Contract\AuthorizerGateway;

final class HttpAuthorizerGateway implements AuthorizerGateway
{
    public function autorizar(): bool
    {
        $response = @file_get_contents('https://util.devi.tools/api/v2/authorize');

        if ($response === false) {
            // Se o serviço estiver fora, eu nego por segurança
            return false;
        }

        $data = json_decode($response, true);

        return isset($data['data']['authorization']) && $data['data']['authorization'] === true;
    }
}
