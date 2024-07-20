<?php

class blowfish
{
    private $Key;

    public function __construct()
    {
        $this->Key = 'sosi_sosison____';
    }

    function Encrypt($payload) {
        $iv  = base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc')));
        $encodedEncryptedData = base64_encode(openssl_encrypt($payload, "AES-128-CBC", $this->Key, OPENSSL_RAW_DATA, base64_decode($iv)));
        return base64_encode($encodedEncryptedData . '::' . $iv);
    }

    function Decrypt($key, $garble) {
        list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
        return openssl_decrypt(base64_decode($encrypted_data), 'AES-128-CBC', $key, 0, base64_decode($iv));
    }
}