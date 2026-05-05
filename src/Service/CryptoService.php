<?php

declare(strict_types=1);

namespace Pdrs\Service;

use RuntimeException;

final class CryptoService
{
    public function __construct(private readonly string $key)
    {
        if (strlen($this->key) < 32) {
            throw new RuntimeException('APP_KEY must be at least 32 bytes after decoding.');
        }
    }

    public function encrypt(string $plainText): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plainText, 'aes-256-gcm', $this->material(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipher === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    public function decrypt(?string $payload): ?string
    {
        if ($payload === null || $payload === '') {
            return null;
        }

        $decoded = base64_decode($payload, true);

        if ($decoded === false || strlen($decoded) < 29) {
            return null;
        }

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $cipher = substr($decoded, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->material(), OPENSSL_RAW_DATA, $iv, $tag);

        return $plain === false ? null : $plain;
    }

    public function hash(string $value): string
    {
        return hash_hmac('sha256', strtolower(trim($value)), $this->material());
    }

    public function sign(string $value): string
    {
        return hash_hmac('sha256', $value, $this->material());
    }

    private function material(): string
    {
        return substr(hash('sha256', $this->key, true), 0, 32);
    }
}
