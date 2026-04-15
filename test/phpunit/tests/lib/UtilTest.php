<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testAddSlashes()
    {
        $this->assertEquals("O\\'Reilly", Util::add_slashes("O'Reilly"));
        $this->assertEquals("test\\\\path", Util::add_slashes("test\\path"));
        $this->assertEquals("test\\\"string", Util::add_slashes('test"string'));
        $this->assertEquals("test\\0string", Util::add_slashes("test\0string"));
        $this->assertEquals("normal string", Util::add_slashes("normal string"));
    }
}
