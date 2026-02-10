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
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $raw = (string) $request->getBody();
            if ($raw !== '') {
                $data = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new Response(
                        400,
                        ['Content-Type' => 'application/json'],
                        json_encode(['error' => 'Invalid JSON'])
                    );
                }
                $request = $request->withAttribute('json', $data);
            }
        }

        return $handler->handle($request);
    }
}
