<?php

declare(strict_types=1);

namespace Tests\Unit\Application\V1;

use App\Application\V1\UseCase\CreateTransfer;
use App\Domain\Contract\AuthorizerGateway;
use App\Domain\Contract\NotifierGateway;
use App\Domain\Contract\TransferRepository;
use App\Domain\Contract\UserRepository;
use App\Domain\Contract\WalletRepository;
use App\Domain\Exception\DomainException;
use App\Infrastructure\Logging\Logger;
use App\Infrastructure\Persistence\TransactionManager;
use PHPUnit\Framework\TestCase;

final class CreateTransferTest extends TestCase
{
    public function testNaoPermiteTransferirParaSiMesmo(): void
    {
        $authorizer = new class implements AuthorizerGateway {
            public function autorizar(): bool
            {
                return true;
            }
        };

        $notifier = new class implements NotifierGateway {
            public function notificar(int $userId, string $mensagem): bool
            {
                return true;
            }
        };

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $tx = new TransactionManager($pdo);


        $users = new class implements UserRepository {
            public function exists(int $userId): bool
            {
                return true;
            }
            public function isMerchant(int $userId): bool
            {
                return false;
            }
        };

        $wallets = new class implements WalletRepository {
            public function getBalanceForUpdate(int $userId): int
            {
                return 10000;
            }
            public function updateBalance(int $userId, int $newBalanceCents): void {}
        };

        $transfers = new class implements TransferRepository {
            public function create(int $payerId, int $payeeId, int $amountCents): int
            {
                return 1;
            }
        };

        $logger = new Logger();

        $useCase = new CreateTransfer($authorizer, $notifier, $tx, $users, $wallets, $transfers, $logger);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Não é permitido transferir para si mesmo.');

        $useCase->executar('10.00', 4, 4);
    }

    public function testSaldoInsuficienteNaoCriaTransferencia(): void
    {
        $authorizer = new class implements AuthorizerGateway {
            public function autorizar(): bool
            {
                return true;
            }
        };

        $notifier = new class implements NotifierGateway {
            public function notificar(int $userId, string $mensagem): bool
            {
                return true;
            }
        };

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $tx = new TransactionManager($pdo);
        $users = new class implements UserRepository {
            public function exists(int $userId): bool
            {
                return true;
            }
            public function isMerchant(int $userId): bool
            {
                return false;
            }
        };

        $wallets = new class implements WalletRepository {
            public function getBalanceForUpdate(int $userId): int
            {
                return 500;
            } // R$ 5,00
            public function updateBalance(int $userId, int $newBalanceCents): void {}
        };

        $transfers = new class implements TransferRepository {
            public int $calls = 0;
            public function create(int $payerId, int $payeeId, int $amountCents): int
            {
                $this->calls++;
                return 1;
            }
        };

        $logger = new Logger();

        $useCase = new CreateTransfer($authorizer, $notifier, $tx, $users, $wallets, $transfers, $logger);

        try {
            $useCase->executar('10.00', 4, 15); // R$ 10,00
            $this->fail('Era esperado DomainException (Saldo insuficiente).');
        } catch (DomainException $e) {
            $this->assertSame('Saldo insuficiente.', $e->getMessage());
            $this->assertSame(0, $transfers->calls, 'Não deveria criar transferência em caso de saldo insuficiente.');
        }
    }
    public function testQuandoNaoAutorizadoNaoCriaTransferencia(): void
    {
        $authorizer = new class implements AuthorizerGateway {
            public function autorizar(): bool
            {
                return false;
            }
        };

        $notifier = new class implements NotifierGateway {
            public function notificar(int $userId, string $mensagem): bool
            {
                return true;
            }
        };

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $tx = new TransactionManager($pdo);

        $users = new class implements UserRepository {
            public function exists(int $userId): bool
            {
                return true;
            }
            public function isMerchant(int $userId): bool
            {
                return false;
            }
        };

        $wallets = new class implements WalletRepository {
            public function getBalanceForUpdate(int $userId): int
            {
                return 999999;
            }
            public function updateBalance(int $userId, int $newBalanceCents): void {}
        };

        $transfers = new class implements TransferRepository {
            public int $calls = 0;
            public function create(int $payerId, int $payeeId, int $amountCents): int
            {
                $this->calls++;
                return 1;
            }
        };

        $logger = new Logger();
        $useCase = new CreateTransfer($authorizer, $notifier, $tx, $users, $wallets, $transfers, $logger);

        try {
            $useCase->executar('10.00', 4, 15);
            $this->fail('Era esperado DomainException (não autorizado).');
        } catch (DomainException $e) {
            $this->assertSame('Transferência não autorizada pelo serviço externo.', $e->getMessage());
            $this->assertSame(0, $transfers->calls, 'Não deveria persistir transferência se não autorizado.');
        }
    }
    public function testUsuarioNaoEncontrado(): void
    {
        $authorizer = new class implements AuthorizerGateway {
            public function autorizar(): bool
            {
                return true;
            }
        };

        $notifier = new class implements NotifierGateway {
            public function notificar(int $userId, string $mensagem): bool
            {
                return true;
            }
        };

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $tx = new TransactionManager($pdo);

        $users = new class implements UserRepository {
            public function exists(int $userId): bool
            {
                return $userId !== 999;
            } // payer inexistente
            public function isMerchant(int $userId): bool
            {
                return false;
            }
        };

        $wallets = new class implements WalletRepository {
            public function getBalanceForUpdate(int $userId): int
            {
                return 10000;
            }
            public function updateBalance(int $userId, int $newBalanceCents): void {}
        };

        $transfers = new class implements TransferRepository {
            public function create(int $payerId, int $payeeId, int $amountCents): int
            {
                return 1;
            }
        };

        $logger = new Logger();
        $useCase = new CreateTransfer($authorizer, $notifier, $tx, $users, $wallets, $transfers, $logger);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Usuário não encontrado.');

        $useCase->executar('10.00', 999, 15);
    }
}
