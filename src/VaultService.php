<?php

declare(strict_types=1);

namespace App;

use DomainException;
use mysqli;

final class VaultService
{
    public function __construct(
        private readonly mysqli $database,
        private readonly VaultCrypto $crypto
    ) {
    }

    public function listForUser(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, service_name, website_url, username_ciphertext, updated_at
             FROM vault_entries WHERE user_id = ? ORDER BY service_name, id DESC'
        );
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $entries = [];

        while ($row = $result->fetch_assoc()) {
            $entries[] = [
                'id' => (int) $row['id'],
                'service_name' => (string) $row['service_name'],
                'website_url' => (string) ($row['website_url'] ?? ''),
                'username' => $this->crypto->decrypt((string) $row['username_ciphertext']),
                'updated_at' => (string) $row['updated_at'],
            ];
        }

        return $entries;
    }

    public function findForUser(int $entryId, int $userId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, service_name, website_url, username_ciphertext, password_ciphertext,
                    notes_ciphertext, created_at, updated_at
             FROM vault_entries WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $statement->bind_param('ii', $entryId, $userId);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();

        if ($row === null) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'service_name' => (string) $row['service_name'],
            'website_url' => (string) ($row['website_url'] ?? ''),
            'username' => $this->crypto->decrypt((string) $row['username_ciphertext']),
            'password' => $this->crypto->decrypt((string) $row['password_ciphertext']),
            'notes' => $this->crypto->decrypt((string) $row['notes_ciphertext']),
            'created_at' => (string) $row['created_at'],
            'updated_at' => (string) $row['updated_at'],
        ];
    }

    public function create(int $userId, array $data): int
    {
        $data = $this->validate($data, true);
        $username = $this->crypto->encrypt($data['username']);
        $password = $this->crypto->encrypt($data['password']);
        $notes = $this->crypto->encrypt($data['notes']);

        $statement = $this->database->prepare(
            'INSERT INTO vault_entries
                (user_id, service_name, website_url, username_ciphertext, password_ciphertext, notes_ciphertext)
             VALUES (?, ?, NULLIF(?, \'\'), ?, ?, ?)'
        );
        $statement->bind_param(
            'isssss',
            $userId,
            $data['service_name'],
            $data['website_url'],
            $username,
            $password,
            $notes
        );
        $statement->execute();

        return (int) $this->database->insert_id;
    }

    public function update(int $entryId, int $userId, array $data): bool
    {
        $existing = $this->findForUser($entryId, $userId);
        if ($existing === null) {
            return false;
        }

        $data = $this->validate($data, false);
        $password = $data['password'] === '' ? $existing['password'] : $data['password'];
        $usernameCiphertext = $this->crypto->encrypt($data['username']);
        $passwordCiphertext = $this->crypto->encrypt($password);
        $notesCiphertext = $this->crypto->encrypt($data['notes']);

        $statement = $this->database->prepare(
            'UPDATE vault_entries
             SET service_name = ?, website_url = NULLIF(?, \'\'), username_ciphertext = ?,
                 password_ciphertext = ?, notes_ciphertext = ?
             WHERE id = ? AND user_id = ?'
        );
        $statement->bind_param(
            'sssssii',
            $data['service_name'],
            $data['website_url'],
            $usernameCiphertext,
            $passwordCiphertext,
            $notesCiphertext,
            $entryId,
            $userId
        );
        $statement->execute();

        return true;
    }

    public function delete(int $entryId, int $userId): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM vault_entries WHERE id = ? AND user_id = ?'
        );
        $statement->bind_param('ii', $entryId, $userId);
        $statement->execute();

        return $statement->affected_rows === 1;
    }

    private function validate(array $data, bool $passwordRequired): array
    {
        $clean = [
            'service_name' => trim((string) ($data['service_name'] ?? '')),
            'website_url' => trim((string) ($data['website_url'] ?? '')),
            'username' => trim((string) ($data['username'] ?? '')),
            'password' => (string) ($data['password'] ?? ''),
            'notes' => trim((string) ($data['notes'] ?? '')),
        ];

        if ($clean['service_name'] === '') {
            throw new DomainException('Informe o nome do serviço.');
        }
        if (strlen($clean['service_name']) > 120) {
            throw new DomainException('O nome do serviço deve ter no máximo 120 caracteres.');
        }
        if ($clean['website_url'] !== '' && (
            strlen($clean['website_url']) > 2048
            || !filter_var($clean['website_url'], FILTER_VALIDATE_URL)
            || !in_array(parse_url($clean['website_url'], PHP_URL_SCHEME), ['http', 'https'], true)
        )) {
            throw new DomainException('Informe uma URL válida, iniciada por http:// ou https://.');
        }
        if (strlen($clean['username']) > 254) {
            throw new DomainException('O usuário deve ter no máximo 254 caracteres.');
        }
        if ($passwordRequired && $clean['password'] === '') {
            throw new DomainException('Informe a senha que será guardada.');
        }
        if (strlen($clean['password']) > 4096) {
            throw new DomainException('A senha deve ter no máximo 4096 caracteres.');
        }
        if (strlen($clean['notes']) > 4000) {
            throw new DomainException('As observações devem ter no máximo 4000 caracteres.');
        }

        return $clean;
    }
}
