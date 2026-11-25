<?php

class Crypto
{
    private static string $encryptionKey = "LEVZ-0516-FRIED-BURGER-CHICKEN-4"; // 32-bit

    private static function getKey(): string
    {
        return hash('sha256', self::$encryptionKey, true);
    }

    // =========================
    // ENCRYPT (for phone, PII, etc.)
    // =========================
    public static function encrypt($data)
    {
        $key = self::getKey();

        $iv = random_bytes(16);

        $ciphertext = openssl_encrypt(
            $data,
            "aes-256-cbc",
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            return null;
        }

        return base64_encode($iv . $ciphertext);
    }

    // =========================
    // DECRYPT (for phone, PII, etc.)
    // =========================
    public static function decrypt($ivCiphertextB64)
    {
        $key = self::getKey();

        $data = base64_decode($ivCiphertextB64, true);

        if ($data === false || strlen($data) < 16) {
            return null;
        }

        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        return openssl_decrypt(
            $ciphertext,
            "aes-256-cbc",
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    // =========================
    // PASSWORD (NO DECRYPTION)
    // =========================
    public static function passwordEncrypt($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function passwordDecrypt($password, $hash)
    {
        return password_verify($password, $hash);
    }
}