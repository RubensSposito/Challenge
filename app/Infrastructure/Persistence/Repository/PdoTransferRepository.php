<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Contract\TransferRepository;

final class PdoTransferRepository implements TransferRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function create(int $payerId, int $payeeId, int $amountCents): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transfers (payer_id, payee_id, amount_cents) VALUES (:payer, :payee, :amount)'
        );
        $stmt->execute([
            'payer' => $payerId,
            'payee' => $payeeId,
            'amount' => $amountCents,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}