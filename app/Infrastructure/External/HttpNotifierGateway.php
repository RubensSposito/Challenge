<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Domain\Contract\NotifierGateway;

final class HttpNotifierGateway implements NotifierGateway
{
    public function notificar(int $userId, string $mensagem): bool
    {
        $payload = json_encode([
            'userId' => $userId,
            'message' => $mensagem
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 2
            ]
        ]);

        $response = @file_get_contents(
            'https://util.devi.tools/api/v1/notify',
            false,
            $context
        );

        return $response !== false;
    }
}
