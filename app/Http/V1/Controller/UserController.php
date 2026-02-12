<?php

declare(strict_types=1);

namespace App\Http\V1\Controller;

use App\Application\V1\UseCase\CreateUser;
use App\Domain\Exception\DomainException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserController
{
    public function __construct(private readonly CreateUser $useCase) {}

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $request->getAttribute('json');

            $user = $this->useCase->executar(
                fullName: (string) ($data['fullName'] ?? ''),
                cpfCnpj: (string) ($data['cpfCnpj'] ?? ''),
                email: (string) ($data['email'] ?? ''),
                password: (string) ($data['password'] ?? ''),
                isMerchant: (bool) ($data['isMerchant'] ?? false),
            );

            return new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode($user)
            );
        } catch (DomainException $e) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['erro' => $e->getMessage()]));
        }
    }
}