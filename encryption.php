<?php
// encryption.php - Utilidade de criptografia para credenciais do KodeWeb Explorer

class KodeWebEncryption {
    private static $key_file = __DIR__ . '/.key';
    private static $cipher = 'aes-256-cbc';

    /**
     * Recupera ou gera a chave de criptografia de 32 bytes (256 bits).
     */
    private static function getKey() {
        if (!file_exists(self::$key_file)) {
            $key = openssl_random_pseudo_bytes(32);
            if ($key === false) {
                $key = random_bytes(32);
            }
            file_put_contents(self::$key_file, $key);
            @chmod(self::$key_file, 0600);
        } else {
            $key = file_get_contents(self::$key_file);
        }
        return $key;
    }

    /**
     * Criptografa dados em texto puro utilizando AES-256-CBC e um IV seguro.
     */
    public static function encrypt($data) {
        $key = self::getKey();
        $iv_length = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        if ($iv === false) {
            $iv = random_bytes($iv_length);
        }
        
        $encrypted = openssl_encrypt($data, self::$cipher, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Descriptografa texto codificado via AES-256-CBC.
     */
    public static function decrypt($data) {
        $key = self::getKey();
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return false;
        }
        
        $iv_length = openssl_cipher_iv_length(self::$cipher);
        if (strlen($decoded) < $iv_length) {
            return false;
        }
        
        $iv = substr($decoded, 0, $iv_length);
        $encrypted = substr($decoded, $iv_length);
        
        return openssl_decrypt($encrypted, self::$cipher, $key, 0, $iv);
    }
}
