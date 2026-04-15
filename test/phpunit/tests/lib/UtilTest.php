<?php

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function setUp(): void
    {
        if (!defined('IN_FORMA')) {
            define('IN_FORMA', true);
        }
        require_once __DIR__ . '/../../../../html/lib/lib.utils.php';
    }

    public function testStripSlashes()
    {
        $this->assertEquals("test", Util::strip_slashes("test"));
        $this->assertEquals("test's", Util::strip_slashes("test's"));
        $this->assertEquals("test\\'s", Util::strip_slashes("test\\'s"));
    }
}
