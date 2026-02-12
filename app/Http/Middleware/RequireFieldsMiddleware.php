<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequireFieldsMiddleware implements MiddlewareInterface
{
    /** @param string[] $required */
    public function __construct(private readonly array $required) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute('json');
        if (!is_array($data)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['erro' => 'JSON não encontrado']));
        }

        $missing = [];
        foreach ($this->required as $field) {
            if (!array_key_exists($field, $data)) {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            return new Response(422, ['Content-Type' => 'application/json'], json_encode([
                'erro' => 'Campos obrigatórios ausentes',
                'campos' => $missing,
            ]));
        }

        return $handler->handle($request);
    }
}