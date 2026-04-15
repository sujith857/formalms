<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    /**
     * @dataProvider purgeDataProvider
     */
    public function testPurge($expected, $input)
    {
        $this->assertEquals($expected, Util::purge($input));
    }

    public function purgeDataProvider()
    {
        return [
            'Plain string' => [
                'hello',
                'hello'
            ],
            'String with HTML tags' => [
                'hello world',
                '<p>hello <b>world</b></p>'
            ],
            'String with single quotes' => [
                'hello \\\'world\\\'',
                "hello 'world'"
            ],
            'String with double quotes' => [
                'hello \"world\"',
                'hello "world"'
            ],
            'String with HTML tags and quotes' => [
                'hello \\\'world\\\'',
                "<p>hello 'world'</p>"
            ],
            'Empty string' => [
                '',
                ''
            ],
            'String with only tags' => [
                '',
                '<p></p><br/>'
            ],
            'String with JS script tag' => [
                'alert(\\\'XSS\\\');',
                "<script>alert('XSS');</script>"
            ],
        ];
    }
}
