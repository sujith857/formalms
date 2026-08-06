<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

class UtilTest extends PHPUnit\Framework\TestCase
{
    public function testArrayColumn()
    {
        $input = [
            ['id' => 1, 'name' => 'Alice', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Bob', 'role' => 'user'],
            ['id' => 3, 'name' => 'Charlie', 'role' => 'user'],
        ];

        // Test with only columnKey
        $expected = ['Alice', 'Bob', 'Charlie'];
        $this->assertEquals($expected, Util::array_column($input, 'name'));

        // Test with columnKey and indexKey
        $expected = [1 => 'Alice', 2 => 'Bob', 3 => 'Charlie'];
        $this->assertEquals($expected, Util::array_column($input, 'name', 'id'));

        // Test missing columnKey
        $this->assertFalse(Util::array_column($input, 'missing_key'));

        // Test missing indexKey
        $this->assertFalse(Util::array_column($input, 'name', 'missing_key'));

        // Test non-scalar indexKey
        $invalidInput = [
            ['id' => [1], 'name' => 'Alice'],
        ];
        $this->assertFalse(Util::array_column($invalidInput, 'name', 'id'));
    }
}
