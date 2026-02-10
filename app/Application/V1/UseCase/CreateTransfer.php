<?php

declare(strict_types=1);

namespace App\Application\V1\UseCase;

use App\Domain\Contract\AuthorizerGateway;
use App\Domain\Contract\NotifierGateway;
use App\Domain\Entity\Transfer;
use App\Domain\Exception\RegraNegocioException;
use App\Domain\Exception\SaldoInsuficienteException;
use App\Domain\ValueObject\Money;

final class CreateTransfer
{
    public function __construct(
        private readonly AuthorizerGateway $authorizer,
        private readonly NotifierGateway $notifier
    ) {}

    public function executar(
        int $payerId,
        int $payeeId,
        string $valorDecimal
    ): Transfer {
        // Aqui eu simulo regras principais (persistência entra depois)
        if ($payerId === $payeeId) {
            throw new RegraNegocioException('Pagador e recebedor não podem ser o mesmo usuário.');
        }

        // Valor
        $valor = Money::fromDecimalString($valorDecimal);

        // Antes de qualquer alteração, eu consulto o serviço autorizador
        if (!$this->authorizer->autorizar()) {
            throw new RegraNegocioException('Transferência não autorizada pelo serviço externo.');
        }

        // Aqui, no MVP, eu simulo sucesso da transferência
        $transfer = new Transfer(
            id: rand(1, 9999),
            payerId: $payerId,
            payeeId: $payeeId,
            valor: $valor,
            status: 'completed',
            criadoEm: gmdate('c')
        );

        // Notificação é efeito colateral: falha não reverte a operação
        $this->notifier->notificar(
            $payeeId,
            'Você recebeu uma transferência.'
        );

        return $transfer;
    }
}
