<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Contract\UserRepository;

final class PdoUserRepository implements UserRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function exists(int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function isMerchant(int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT is_merchant FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();
        return $val !== false && (int) $val === 1;
    }
}