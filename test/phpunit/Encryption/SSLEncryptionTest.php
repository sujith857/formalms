<?php

namespace FormaLms\lib\Encryption;

use PHPUnit\Framework\TestCase;

class SSLEncryptionTest extends TestCase
{
    public function testEncryptDecryptString()
    {
        $originalString = "Hello, World! 12345 @#$%";

        $encryptedString = SSLEncryption::encryptString($originalString);
        $this->assertNotEquals($originalString, $encryptedString);
        $this->assertNotEmpty($encryptedString);

        $decryptedString = SSLEncryption::decryptString($encryptedString);
        $this->assertEquals($originalString, $decryptedString);
    }

    public function testEncryptDecryptEmptyString()
    {
        $originalString = "";

        $encryptedString = SSLEncryption::encryptString($originalString);

        $decryptedString = SSLEncryption::decryptString($encryptedString);
        $this->assertEquals($originalString, $decryptedString);
    }

    public function testEncryptDecryptSpecialCharacters()
    {
        $originalString = "¡™£¢∞§¶•ªº–≠œ∑´®†¥¨ˆøπ“‘«åß∂ƒ©˙∆˚¬…æΩ≈ç√∫˜µ≤≥÷";

        $encryptedString = SSLEncryption::encryptString($originalString);
        $this->assertNotEquals($originalString, $encryptedString);
        $this->assertNotEmpty($encryptedString);

        $decryptedString = SSLEncryption::decryptString($encryptedString);
        $this->assertEquals($originalString, $decryptedString);
    }

    public function testEncryptDecryptLongString()
    {
        $originalString = str_repeat("A long string with some repeating content. ", 1000);

        $encryptedString = SSLEncryption::encryptString($originalString);
        $this->assertNotEquals($originalString, $encryptedString);
        $this->assertNotEmpty($encryptedString);

        $decryptedString = SSLEncryption::decryptString($encryptedString);
        $this->assertEquals($originalString, $decryptedString);
    }
}
