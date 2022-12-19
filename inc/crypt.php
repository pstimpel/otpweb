<?php
/*
 * https://stackoverflow.com/questions/2604435/fatal-error-call-to-undefined-function-mcrypt-encrypt
 *
 * Step-1: Download a suitable version for your system from here: https://pecl.php.net/package/mcrypt/1.0.3/windows

Step-2: Unzip and copy php_mcrypt.dll file to ../xampp/php/ext/

Step-3: Open ../xampp/php/php.ini file and add a line extension=php_mcrypt.dll

Step-4: Restart apache, DONE!

------------------>Not used, deprecated in php7.1, using openssl now


 * */


class Crypt
{

    /**
     * Test function for encryption
     *//*
    function cryptTestEncryption() {
        $password = "myPassword_!";
        $messageClear = "Secret message";

        // 32 byte binary blob
        $aes256Key = hash("SHA256", $password, true);


        $iv = Crypt::createIV();

        $crypted = Crypt::encrypt_base64($messageClear, $aes256Key, $iv);

        $newClear = Crypt::decrypt($crypted, $aes256Key, $iv);

        echo
            //"IV:        <code>".$iv."</code><br/>".
            "IV:        <code>".$iv."</code><br/>".
            "IV B64:        <code>".base64_encode($iv)."</code><br/>".
            "Encrypred B64: <code>".$crypted."</code><br/>".
            "Encrypred: <code>".base64_decode($crypted)."</code><br/>".
            "Decrypred: <code>".$newClear."</code><br/>";
    }
    */

    /**
     * Creates random IV
     *
     * @return string The IV, string of bytes
     */
    public static function createIV(): string
    {
        //binary return value
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    }

    /**
     * Enrypt a value
     *
     * @param string $data The data to encrypt
     * @param string $passwordHash_sha256 The string of bytes representing the sha256 hashed password
     * @param string $iv The string of bytes representing the IV
     *
     * @return string the encrypted string, base64 encoded
     */
    public static function encrypt_base64(string $data, string $passwordHash_sha256, string $iv): string
    {
        //base64 encoded return value
        return openssl_encrypt($data, 'aes-256-cbc', $passwordHash_sha256, 0, $iv);
    }

    /**
     * Decrypt a value
     *
     * @param string $encryptedData_base64 The base64_encoded data to decrypt
     * @param string $passwordHash_sha256 The string of bytes representing the sha256 hashed password
     * @param string $iv The string of bytes representing the IV
     *
     * @return string the decrapted string
     */
    public static function decrypt(string $encryptedData_base64, string $passwordHash_sha256, string $iv): string
    {
        // clear text return value
        return openssl_decrypt($encryptedData_base64, 'aes-256-cbc', $passwordHash_sha256, 0, $iv);
    }

}