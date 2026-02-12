<?php

declare(strict_types=1);

namespace App\Application\V1\UseCase;

use App\Domain\Contract\UserWriterRepository;
use App\Domain\Exception\DomainException;

final class CreateUser
{
    public function __construct(private readonly UserWriterRepository $users) {}

    /**
     * @return array{id:int, fullName:string, cpfCnpj:string, email:string, isMerchant:bool}
     */
    public function executar(
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $password,
        bool $isMerchant
    ): array {
        $cpfCnpj = preg_replace('/\D+/', '', $cpfCnpj) ?? '';
        $email = strtolower(trim($email));

        if ($fullName === '' || $cpfCnpj === '' || $email === '' || $password === '') {
            throw new DomainException('Campos obrigatórios ausentes.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('E-mail inválido.');
        }

        if ($this->users->emailExists($email)) {
            throw new DomainException('E-mail já cadastrado.');
        }

        if ($this->users->cpfCnpjExists($cpfCnpj)) {
            throw new DomainException('CPF/CNPJ já cadastrado.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->users->create($fullName, $cpfCnpj, $email, $hash, $isMerchant);
        $this->users->ensureWallet($id, 0);

        return [
            'id' => $id,
            'fullName' => $fullName,
            'cpfCnpj' => $cpfCnpj,
            'email' => $email,
            'isMerchant' => $isMerchant,
        ];
    }
}