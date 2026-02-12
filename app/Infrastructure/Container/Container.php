<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use App\Http\Router;
use App\Infrastructure\Persistence\PdoConnectionFactory;
use App\Infrastructure\Persistence\TransactionManager;

// Contracts
use App\Domain\Contract\UserRepository;
use App\Domain\Contract\WalletRepository;
use App\Domain\Contract\TransferRepository;
use App\Domain\Contract\UserWriterRepository;

// Infra repos
use App\Infrastructure\Persistence\Repository\PdoUserRepository;
use App\Infrastructure\Persistence\Repository\PdoWalletRepository;
use App\Infrastructure\Persistence\Repository\PdoTransferRepository;
use App\Infrastructure\Persistence\Repository\PdoUserWriterRepository;

// External gateways
use App\Infrastructure\External\HttpAuthorizerGateway;
use App\Infrastructure\External\HttpNotifierGateway;

// UseCases
use App\Application\V1\UseCase\CreateTransfer;
use App\Application\V1\UseCase\CreateUser;

// Controllers
use App\Http\V1\Controller\TransferController;
use App\Http\V1\Controller\UserController;

// Logging
use App\Infrastructure\Logging\Logger;

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
        | Infra (DB / Transaction / Repositories)
        |---------------------------------------------------------
        */
        $c->set(\PDO::class, fn () => PdoConnectionFactory::fromEnv());

        $c->set(TransactionManager::class, fn (self $c) => new TransactionManager(
            $c->get(\PDO::class)
        ));

        $c->set(UserRepository::class, fn (self $c) => new PdoUserRepository(
            $c->get(\PDO::class)
        ));

        $c->set(WalletRepository::class, fn (self $c) => new PdoWalletRepository(
            $c->get(\PDO::class)
        ));

        $c->set(TransferRepository::class, fn (self $c) => new PdoTransferRepository(
            $c->get(\PDO::class)
        ));

        // Writer repo (cadastro)
        $c->set(UserWriterRepository::class, fn (self $c) => new PdoUserWriterRepository(
            $c->get(\PDO::class)
        ));

        /*
        |---------------------------------------------------------
        | Logging
        |---------------------------------------------------------
        */
        $c->set(Logger::class, fn () => new Logger());

        /*
        |---------------------------------------------------------
        | External gateways
        |---------------------------------------------------------
        */
        $c->set(HttpAuthorizerGateway::class, fn () => new HttpAuthorizerGateway());
        $c->set(HttpNotifierGateway::class, fn () => new HttpNotifierGateway());

        /*
        |---------------------------------------------------------
        | Casos de uso (Application)
        |---------------------------------------------------------
        */
        $c->set(CreateTransfer::class, fn (self $c) => new CreateTransfer(
            $c->get(HttpAuthorizerGateway::class),
            $c->get(HttpNotifierGateway::class),
            $c->get(TransactionManager::class),
            $c->get(UserRepository::class),
            $c->get(WalletRepository::class),
            $c->get(TransferRepository::class),
            $c->get(Logger::class),
        ));

        $c->set(CreateUser::class, fn (self $c) => new CreateUser(
            $c->get(UserWriterRepository::class)
        ));

        /*
        |---------------------------------------------------------
        | Controllers
        |---------------------------------------------------------
        */
        $c->set(TransferController::class, fn (self $c) => new TransferController(
            $c->get(CreateTransfer::class)
        ));

        $c->set(UserController::class, fn (self $c) => new UserController(
            $c->get(CreateUser::class)
        ));

        /*
        |---------------------------------------------------------
        | Router + Rotas
        |---------------------------------------------------------
        */
        $c->set(
            Router::class,
            fn (self $c) => new Router(
                [
                    'GET /health' => [\App\Http\Controller\HealthController::class, 'index'],

                    'POST /transfer' => [TransferController::class, 'create'],
                    'POST /users' => [UserController::class, 'create'],
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
            $this->instances[$id] = new $id();
            return $this->instances[$id];
        }

        $this->instances[$id] = ($this->definitions[$id])($this);
        return $this->instances[$id];
    }
}