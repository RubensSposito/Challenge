<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Http\Router;

final class Container
{
    /** @var array<class-string, callable(self): mixed> */
    private array $definitions = [];

    /** @var array<class-string, mixed> */
    private array $instances = [];

    public static function build(): self
    {
        $c = new self();

        /*
        |---------------------------------------------------------
        | Casos de uso (Application)
        |---------------------------------------------------------
        | Aqui eu registro meus serviços de aplicação.
        */
        $c->set(
            \App\Application\V1\UseCase\CreateTransfer::class,
            fn () => new \App\Application\V1\UseCase\CreateTransfer(
                new \App\Infrastructure\External\HttpAuthorizerGateway(),
                new \App\Infrastructure\External\HttpNotifierGateway()
            )
        );

        /*
        |---------------------------------------------------------
        | Controllers
        |---------------------------------------------------------
        | Aqui eu monto os controllers com suas dependências.
        */
        $c->set(
            \App\Http\V1\Controller\TransferController::class,
            fn (self $c) => new \App\Http\V1\Controller\TransferController(
                $c->get(\App\Application\V1\UseCase\CreateTransfer::class)
            )
        );

        // Aqui eu deixo um controller V2 placeholder registrado (gancho de evolução)
        $c->set(
            \App\Http\V2\Controller\TransferController::class,
            fn () => new \App\Http\V2\Controller\TransferController()
        );

        /*
        |---------------------------------------------------------
        | Router + Rotas
        |---------------------------------------------------------
        | Aqui eu centralizo o mapeamento de rotas e deixo o Router
        | resolver controllers via Container (DI sem framework).
        */
        $c->set(
            Router::class,
            fn (self $c) => new Router(
                [
                    'GET /health' => [\App\Http\Controller\HealthController::class, 'index'],

                    // V1 — contrato do desafio
                    'POST /transfer' => [\App\Http\V1\Controller\TransferController::class, 'create'],

                    // V2 — proposta futura (ainda não implementada de verdade)
                    'POST /transfers2.0' => [\App\Http\V2\Controller\TransferController::class, 'create'],
                ],
                fn (string $class) => $c->get($class)
            )
        );

        return $c;
    }

    public function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->definitions[$id])) {
            // Aqui eu tenho um fallback simples: instancio sem dependências.
            $this->instances[$id] = new $id();
            return $this->instances[$id];
        }

        $this->instances[$id] = ($this->definitions[$id])($this);
        return $this->instances[$id];
    }
}
