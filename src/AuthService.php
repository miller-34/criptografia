<?php

declare(strict_types=1);

namespace App;

use DomainException;
use mysqli;
use mysqli_sql_exception;

final class AuthService
{
    public function __construct(private readonly mysqli $database)
    {
    }

    public function register(string $email, string $password): void
    {
        $email = $this->normalizeEmail($email);
        $this->validateCredentials($email, $password);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new \RuntimeException('Não foi possível proteger a senha.');
        }

        try {
            $statement = $this->database->prepare(
                'INSERT INTO users (email, password_hash) VALUES (?, ?)'
            );
            $statement->bind_param('ss', $email, $passwordHash);
            $statement->execute();
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() === 1062) {
                throw new DomainException('Este e-mail já está cadastrado.');
            }

            throw $exception;
        }
    }

    /** @return array{id: int, email: string}|null */
    public function authenticate(string $email, string $password): ?array
    {
        $email = $this->normalizeEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return null;
        }

        $statement = $this->database->prepare(
            'SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1'
        );
        $statement->bind_param('s', $email);
        $statement->execute();
        $result = $statement->get_result();
        $user = $result->fetch_assoc();

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->rehashPassword((int) $user['id'], $password);
        }

        return [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
        ];
    }

    private function validateCredentials(string $email, string $password): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Informe um e-mail válido.');
        }

        $passwordLength = strlen($password);
        if ($passwordLength < 8) {
            throw new DomainException('A senha deve ter pelo menos 8 caracteres.');
        }

        if ($passwordLength > 72) {
            throw new DomainException('A senha deve ter no máximo 72 caracteres.');
        }
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function rehashPassword(int $userId, string $password): void
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            return;
        }

        $statement = $this->database->prepare(
            'UPDATE users SET password_hash = ? WHERE id = ?'
        );
        $statement->bind_param('si', $passwordHash, $userId);
        $statement->execute();
    }
}
