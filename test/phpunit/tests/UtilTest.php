<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    /**
     * @dataProvider addSlashesProvider
     */
    public function testAddSlashes($input, $expected)
    {
        $this->assertEquals($expected, Util::add_slashes($input));
    }

    public function addSlashesProvider()
    {
        return [
            ['O\'Reilly', 'O\\\'Reilly'],
            ['"hello"', '\\"hello\\"'],
            ['\\backslash', '\\\\backslash'],
            ["null\0byte", "null\\0byte"],
            ['normal string', 'normal string'],
            ['', ''],
        ];
    }
}
