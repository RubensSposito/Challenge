<?php

declare(strict_types=1);

namespace App\Application\V1\UseCase;

use App\Domain\Contract\AuthorizerGateway;
use App\Domain\Contract\NotifierGateway;
use App\Domain\Contract\UserRepository;
use App\Domain\Contract\WalletRepository;
use App\Domain\Contract\TransferRepository;
use App\Domain\Exception\DomainException;
use App\Domain\ValueObject\Money;
use App\Domain\Entity\Transfer;
use App\Infrastructure\Persistence\TransactionManager;
use App\Infrastructure\Logging\Logger;

final class CreateTransfer
{
    public function __construct(
        private readonly AuthorizerGateway $authorizer,
        private readonly NotifierGateway $notifier,
        private readonly TransactionManager $tx,
        private readonly UserRepository $users,
        private readonly WalletRepository $wallets,
        private readonly TransferRepository $transfers,
        private readonly Logger $logger,
    ) {}

    public function executar(
        string $valor,
        int $payer,
        int $payee
    ): Transfer {

        // 1️⃣ Regra: não pode transferir para si mesmo
        if ($payer === $payee) {
            throw new DomainException('Não é permitido transferir para si mesmo.');
        }

        // 2️⃣ Regra: usuários precisam existir
        if (!$this->users->exists($payer) || !$this->users->exists($payee)) {
            throw new DomainException('Usuário não encontrado.');
        }

        // 3️⃣ Regra: lojista não pode enviar
        if ($this->users->isMerchant($payer)) {
            throw new DomainException('Lojista não pode realizar transferências.');
        }

        // 4️⃣ Converter valor usando ValueObject
        $money = Money::fromDecimalString($valor);
        $amountCents = $money->toCents();

        // 5️⃣ Autorizador externo
        if (!$this->authorizer->autorizar()) {
            throw new DomainException('Transferência não autorizada pelo serviço externo.');
        }

        $this->logger->info('transfer.authorized', [
            'payer' => $payer,
            'payee' => $payee,
            'amountCents' => $amountCents,
        ]);

        // 6️⃣ Transação com lock
        $transferId = $this->tx->transactional(function () use ($payer, $payee, $amountCents) {

            $payerBalance = $this->wallets->getBalanceForUpdate($payer);
            $payeeBalance = $this->wallets->getBalanceForUpdate($payee);

            // 🔴 Aqui está a comparação correta
            if ($payerBalance < $amountCents) {
                throw new DomainException('Saldo insuficiente.');
            }

            $this->wallets->updateBalance($payer, $payerBalance - $amountCents);
            $this->wallets->updateBalance($payee, $payeeBalance + $amountCents);

            return $this->transfers->create($payer, $payee, $amountCents);
        });

        $this->logger->info('transfer.created', [
            'transferId' => $transferId,
            'payer' => $payer,
            'payee' => $payee,
            'amountCents' => $amountCents,
        ]);

        // 7️⃣ Criar entidade de retorno
        $transfer = new Transfer(
            id: $transferId,
            payerId: $payer,
            payeeId: $payee,
            valor: $money,
            status: 'completed',
            criadoEm: gmdate('c')
        );

        // 8️⃣ Notificação best-effort
        try {
            $this->notifier->notificar(
                $payee,
                'Você recebeu uma transferência.'
            );
        } catch (\Throwable) {
            $this->logger->error('notify.failed', [
                'payee' => $payee
            ]);
        }

        return $transfer;
    }
}