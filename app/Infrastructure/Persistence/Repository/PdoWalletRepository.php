<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Contract\WalletRepository;

final class PdoWalletRepository implements WalletRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function getBalanceForUpdate(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT balance_cents FROM wallets WHERE user_id = :id FOR UPDATE'
        );
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();

        if ($val === false) {
            throw new \RuntimeException("Wallet not found for user {$userId}");
        }

        return (int) $val;
    }

    public function updateBalance(int $userId, int $newBalanceCents): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE wallets SET balance_cents = :b WHERE user_id = :id'
        );
        $stmt->execute(['b' => $newBalanceCents, 'id' => $userId]);
    }
}