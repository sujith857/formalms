<?php

use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function setUp()
    {
        if (!defined('IN_FORMA')) {
            define('IN_FORMA', true);
        }
        require_once __DIR__ . '/../../../../../html/lib/lib.utils.php';
    }

    public function serializeProvider()
    {
        return [
            ['Empty string', '', '""'],
            ['String', 'hello', '"hello"'],
            ['Integer', 123, '123'],
            ['Float', 1.23, '1.23'],
            ['Boolean true', true, 'true'],
            ['Boolean false', false, 'false'],
            ['Null', null, 'null'],
            ['Array numeric', [1, 2, 3], '[1,2,3]'],
            ['Array associative', ['a' => 1, 'b' => 2], '{"a":1,"b":2}'],
            ['Nested array', ['a' => [1, 2], 'b' => ['c' => 3]], '{"a":[1,2],"b":{"c":3}}'],
            ['Object', (object)['a' => 1, 'b' => 2], '{"a":1,"b":2}'],
        ];
    }

    /**
     * @dataProvider serializeProvider
     */
    public function testSerialize($name, $input, $expected)
    {
        $this->assertEquals($expected, Util::serialize($input), "Failed asserting that serialize() works for: $name");
    }
}
