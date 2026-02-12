<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface UserWriterRepository
{
    public function emailExists(string $email): bool;
    public function cpfCnpjExists(string $cpfCnpj): bool;

    /** @return int ID criado */
    public function create(
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $passwordHash,
        bool $isMerchant
    ): int;

    public function ensureWallet(int $userId, int $initialBalanceCents = 0): void;
}