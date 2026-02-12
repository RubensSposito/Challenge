<?php

declare(strict_types=1);

namespace App\Http\V1\Controller;

use App\Application\V1\UseCase\CreateTransfer;
use App\Domain\Exception\DomainException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TransferController
{
    public function __construct(private readonly CreateTransfer $useCase) {}

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getAttribute('json');

            $transfer = $this->useCase->executar(
                valor: (string) ($data['value'] ?? ''),
                payer: (int) ($data['payer'] ?? 0),
                payee: (int) ($data['payee'] ?? 0),
            );

            return new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode($transfer, JSON_UNESCAPED_UNICODE)
            );
        } catch (DomainException $e) {
            return new Response(
                422,
                ['Content-Type' => 'application/json'],
                json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE)
            );
        }
    }
}