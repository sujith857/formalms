<?php

define('IN_FORMA', true);
require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testUnserializeValidJsonArray()
    {
        $json = '{"key":"value"}';
        $expected = ['key' => 'value'];
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeValidJsonString()
    {
        $json = '"hello world"';
        $expected = 'hello world';
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeValidJsonNumber()
    {
        $json = '42';
        $expected = 42;
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeValidJsonBoolean()
    {
        $json = 'true';
        $expected = true;
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeInvalidJson()
    {
        $json = '{invalid_json}';
        $expected = null;
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeEmptyString()
    {
        $json = '';
        $expected = null;
        $this->assertEquals($expected, Util::unserialize($json));
    }

    public function testUnserializeNull()
    {
        $json = 'null';
        $expected = null;
        $this->assertEquals($expected, Util::unserialize($json));
    }
}
