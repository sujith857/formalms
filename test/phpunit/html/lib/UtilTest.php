<?php

define('IN_FORMA', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    public function testUnserializeValidJson()
    {
        $this->assertEquals(['foo' => 'bar'], Util::unserialize('{"foo":"bar"}'));
        $this->assertEquals([1, 2, 3], Util::unserialize('[1, 2, 3]'));
        $this->assertEquals(null, Util::unserialize('null'));
        $this->assertEquals(true, Util::unserialize('true'));
        $this->assertEquals(123, Util::unserialize('123'));
        $this->assertEquals("hello", Util::unserialize('"hello"'));
    }

    public function testUnserializeInvalidJson()
    {
        $this->assertEquals(null, Util::unserialize('{"foo":"bar"')); // missing brace
        $this->assertEquals(null, Util::unserialize('undefined')); // invalid json
        $this->assertEquals(null, Util::unserialize('')); // empty string
    }
}
