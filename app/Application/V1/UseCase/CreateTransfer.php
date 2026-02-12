<?php

declare(strict_types=1);

namespace App\Application\V1\UseCase;

use App\Domain\Contract\AuthorizerGateway;
use App\Domain\Contract\NotifierGateway;
use App\Domain\Entity\Transfer;
use App\Domain\Exception\RegraNegocioException;
use App\Domain\ValueObject\Money;

final class CreateTransfer
{
    public function __construct(
        private readonly AuthorizerGateway $authorizer,
        private readonly NotifierGateway $notifier
    ) {}

    public function executar(
        string $valor,
        int $payer,
        int $payee
    ): Transfer {
        if ($payer === $payee) {
            throw new RegraNegocioException('Pagador e recebedor não podem ser o mesmo usuário.');
        }

        $money = Money::fromDecimalString($valor);

        if (!$this->authorizer->autorizar()) {
            throw new RegraNegocioException('Transferência não autorizada pelo serviço externo.');
        }

        $transfer = new Transfer(
            id: rand(1, 9999),
            payerId: $payer,
            payeeId: $payee,
            valor: $money,
            status: 'completed',
            criadoEm: gmdate('c')
        );

        // notificação best-effort (se falhar, não reverte)
        $this->notifier->notificar($payee, 'Você recebeu uma transferência.');

        return $transfer;
    }
}