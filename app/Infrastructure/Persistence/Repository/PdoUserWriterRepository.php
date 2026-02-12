<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Contract\UserWriterRepository;

final class PdoUserWriterRepository implements UserWriterRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function cpfCnpjExists(string $cpfCnpj): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE cpf_cnpj = :cpf');
        $stmt->execute(['cpf' => $cpfCnpj]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $passwordHash,
        bool $isMerchant
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (full_name, cpf_cnpj, email, password_hash, is_merchant)
             VALUES (:n, :cpf, :e, :p, :m)'
        );

        $stmt->execute([
            'n' => $fullName,
            'cpf' => $cpfCnpj,
            'e' => $email,
            'p' => $passwordHash,
            'm' => $isMerchant ? 1 : 0,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function ensureWallet(int $userId, int $initialBalanceCents = 0): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO wallets (user_id, balance_cents)
             VALUES (:id, :b)
             ON DUPLICATE KEY UPDATE balance_cents = balance_cents'
        );
        $stmt->execute(['id' => $userId, 'b' => $initialBalanceCents]);
    }
}