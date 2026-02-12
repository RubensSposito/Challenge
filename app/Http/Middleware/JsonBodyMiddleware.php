<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonBodyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            return $handler->handle($request);
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if ($contentType === '' || stripos($contentType, 'application/json') === false) {
            return new Response(415, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'Content-Type deve ser application/json'
            ]));
        }

        $raw = (string) $request->getBody();

        if (trim($raw) === '') {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'Body JSON vazio'
            ]));
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'JSON inválido'
            ]));
        }

        if (!is_array($data)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'JSON deve ser um objeto'
            ]));
        }

        return $handler->handle($request->withAttribute('json', $data));
    }
}