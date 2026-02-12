<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cid = $request->getHeaderLine('X-Correlation-Id');
        if ($cid === '') {
            $cid = bin2hex(random_bytes(16));
        }

        $request = $request->withAttribute('correlation_id', $cid);

        $response = $handler->handle($request);

        return $response->withHeader('X-Correlation-Id', $cid);
    }
}
