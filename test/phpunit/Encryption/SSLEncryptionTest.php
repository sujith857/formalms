<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../html/lib/Encryption/SSLEncryption.php';

use FormaLms\lib\Encryption\SSLEncryption;
use PHPUnit\Framework\TestCase;

class SSLEncryptionTest extends TestCase
{
    public function testEncryptString()
    {
        $input = "test_string";
        $encrypted = SSLEncryption::encryptString($input);

        $this->assertNotEquals($input, $encrypted);
        $this->assertNotEmpty($encrypted);

        $decrypted = SSLEncryption::decryptString($encrypted);
        $this->assertEquals($input, $decrypted);

        // Test with empty string
        $inputEmpty = "";
        $encryptedEmpty = SSLEncryption::encryptString($inputEmpty);
        $this->assertNotEquals($encrypted, $encryptedEmpty);

        $decryptedEmpty = SSLEncryption::decryptString($encryptedEmpty);
        $this->assertEquals($inputEmpty, $decryptedEmpty);

        // Test with special characters
        $inputSpecial = "test_string!@#$%^&*()_+{}|:\"<>?-=[]\;',./";
        $encryptedSpecial = SSLEncryption::encryptString($inputSpecial);
        $this->assertNotEquals($inputSpecial, $encryptedSpecial);
        $this->assertNotEmpty($encryptedSpecial);

        $decryptedSpecial = SSLEncryption::decryptString($encryptedSpecial);
        $this->assertEquals($inputSpecial, $decryptedSpecial);
    }

    public function testDecryptString()
    {
        $input = "another_string";
        $encrypted = SSLEncryption::encryptString($input);
        $decrypted = SSLEncryption::decryptString($encrypted);

        $this->assertEquals($input, $decrypted);
    }

    public function testEncryptDownloadUrl()
    {
        $url = "https://example.com/download?file=123";
        $encryptedUrl = SSLEncryption::encrpytDownloadUrl($url);

        $this->assertNotEquals($url, $encryptedUrl);
        $this->assertNotEmpty($encryptedUrl);

        // Ensure it uses custom base64 encoding (replacing = with @)
        $this->assertFalse(strpos($encryptedUrl, '='));

        $decryptedUrl = SSLEncryption::decryptDownloadUrl($encryptedUrl);
        $this->assertEquals($url, $decryptedUrl);
    }

    public function testDecryptDownloadUrl()
    {
        $url = "https://example.com/download?file=456";
        $encryptedUrl = SSLEncryption::encrpytDownloadUrl($url);
        $decryptedUrl = SSLEncryption::decryptDownloadUrl($encryptedUrl);

        $this->assertEquals($url, $decryptedUrl);
    }
}
