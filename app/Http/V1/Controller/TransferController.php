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
    public function __construct(
        private readonly CreateTransfer $useCase
    ) {}

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getAttribute('json');

            $transfer = $this->useCase->executar(
                payerId: (int) $data['payer'],
                payeeId: (int) $data['payee'],
                valorDecimal: (string) $data['value']
            );

            return new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'id' => $transfer->id,
                    'status' => 'Transferência realizada com sucesso',
                    'valor' => $transfer->valor->toDecimal(),
                    'payer' => $transfer->payerId,
                    'payee' => $transfer->payeeId,
                    'criadoEm' => $transfer->criadoEm
                ])
            );
        } catch (DomainException $e) {
            return new Response(
                400,
                ['Content-Type' => 'application/json'],
                json_encode(['erro' => $e->getMessage()])
            );
        }
    }
}
