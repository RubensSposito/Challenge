<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

final class TransactionManager
{
    public function __construct(private readonly \PDO $pdo) {}

    /**
     * @template T
     * @param callable():T $fn
     * @return T
     */
    public function transactional(callable $fn)
    {
        $this->pdo->beginTransaction();
        try {
            $result = $fn();
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function pdo(): \PDO
    {
        return $this->pdo;
    }
}