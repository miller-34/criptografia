<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class VaultCrypto
{
    private string $key;

    public function __construct(string $encodedKey)
    {
        $key = base64_decode($encodedKey, true);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('APP_KEY deve ser uma chave de 32 bytes codificada em Base64.');
        }

        $this->key = $key;
    }

    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);

        return base64_encode($nonce . $ciphertext);
    }

    public function decrypt(string $encodedCiphertext): string
    {
        $payload = base64_decode($encodedCiphertext, true);
        if ($payload === false || strlen($payload) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new RuntimeException('Dado criptografado inválido.');
        }

        $nonce = substr($payload, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($payload, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);

        if ($plaintext === false) {
            throw new RuntimeException('Não foi possível descriptografar o dado. Verifique a APP_KEY.');
        }

        return $plaintext;
    }
}
