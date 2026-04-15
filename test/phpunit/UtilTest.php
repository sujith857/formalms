<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    public function testCutShortString()
    {
        $this->assertEquals('short', Util::cut('short'));
    }

    public function testCutLongStringDefault()
    {
        $longString = str_repeat('a', 150);
        $expected = str_repeat('a', 100) . ' ...';
        $this->assertEquals($expected, Util::cut($longString));
    }

    public function testCutLongStringCustomMaxChar()
    {
        $longString = str_repeat('a', 50);
        $expected = str_repeat('a', 10) . ' ...';
        $this->assertEquals($expected, Util::cut($longString, 10));
    }

    public function testCutStripsHtmlTags()
    {
        $htmlString = '<b>short</b>';
        $this->assertEquals('short', Util::cut($htmlString));
    }

    public function testCutLongHtmlStringStripsTagsBeforeLengthCheck()
    {
        // The total string is long, but without tags it's short
        $htmlString = '<p>' . str_repeat('a', 10) . '</p>';
        $this->assertEquals(str_repeat('a', 10), Util::cut($htmlString, 20));
    }

    public function testCutLongHtmlStringStripsTagsAndTruncates()
    {
        // The total string is long, and even without tags it's long
        $htmlString = '<p>' . str_repeat('a', 150) . '</p>';
        $expected = str_repeat('a', 100) . ' ...';
        $this->assertEquals($expected, Util::cut($htmlString));
    }

    public function testCutEmptyString()
    {
        $this->assertEquals('', Util::cut(''));
    }

    public function testCutOnlyHtmlTags()
    {
        $this->assertEquals('', Util::cut('<b></b><p></p>'));
    }

    public function testCutExactLengthString()
    {
        $exactString = str_repeat('a', 100);
        $this->assertEquals($exactString, Util::cut($exactString));
    }
}
