<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    public function testPurgeBasicString()
    {
        $input = "Hello World";
        $expected = "Hello World";
        $this->assertEquals($expected, Util::purge($input));
    }

    public function testPurgeStripsHtmlTags()
    {
        $input = "<p>Hello <b>World</b></p>";
        $expected = "Hello World";
        $this->assertEquals($expected, Util::purge($input));
    }

    public function testPurgeAddsSlashesToQuotes()
    {
        $input = "It's a \"beautiful\" day";
        $expected = "It\'s a \\\"beautiful\\\" day";
        $this->assertEquals($expected, Util::purge($input));
    }

    public function testPurgeCombinedHtmlAndQuotes()
    {
        $input = "<a href='http://example.com'>Click \"here\"</a>";
        $expected = "Click \\\"here\\\"";
        $this->assertEquals($expected, Util::purge($input));
    }

    public function testPurgeWithMaliciousScript()
    {
        $input = "<script>alert('xss');</script>";
        $expected = "alert(\\'xss\\');";
        $this->assertEquals($expected, Util::purge($input));
    }
}
