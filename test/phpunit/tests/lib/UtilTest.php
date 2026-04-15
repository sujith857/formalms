<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    public function testCutWithShortString()
    {
        $this->assertEquals('short', Util::cut('short'));
    }

    public function testCutWithLongString()
    {
        $longStr = str_repeat('a', 105);
        $expected = str_repeat('a', 100) . ' ...';
        $this->assertEquals($expected, Util::cut($longStr));
    }

    public function testCutWithCustomMaxChar()
    {
        $this->assertEquals('12345 ...', Util::cut('1234567890', 5));
    }

    public function testCutStripsTags()
    {
        $htmlStr = '<b>bold</b> text';
        $this->assertEquals('bold text', Util::cut($htmlStr));
    }

    public function testCutStripsTagsAndTruncates()
    {
        $htmlStr = '<b>bold</b> text that is quite long';
        $this->assertEquals('bold text ...', Util::cut($htmlStr, 9));
    }

    public function testCutWithEmptyString()
    {
        $this->assertEquals('', Util::cut(''));
    }

    public function testCutWithExactMaxLength()
    {
        $str = str_repeat('a', 100);
        $this->assertEquals($str, Util::cut($str));
    }
}
