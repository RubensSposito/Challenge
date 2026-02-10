<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthController
{
    public function index(ServerRequestInterface $request, array $vars = []): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'ok']));
    }
}
