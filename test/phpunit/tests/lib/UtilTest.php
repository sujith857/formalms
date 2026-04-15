<?php

define('IN_FORMA', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

class UtilTest extends TestCase
{
    public function testHighlightReturnsStringWhenKeyIsEmpty()
    {
        $this->assertEquals('empty key', Util::highlight('empty key', ''));
        $this->assertEquals('empty key', Util::highlight('empty key', null));
    }

    public function testHighlightReplacesKeyWithSpan()
    {
        $this->assertEquals('<span class="highlight">test</span>', Util::highlight('test', 'test'));
    }

    public function testHighlightCaseInsensitive()
    {
        $this->assertEquals('<span class="highlight">Test</span>', Util::highlight('Test', 'test'));
        $this->assertEquals('<span class="highlight">test</span>', Util::highlight('test', 'Test'));
    }

    public function testHighlightReturnsOriginalStringWhenNoMatch()
    {
        $this->assertEquals('no match', Util::highlight('no match', 'test'));
    }

    public function testHighlightWithCustomClassname()
    {
        $this->assertEquals('<span class="custom">test</span>', Util::highlight('test', 'test', 'custom'));
    }

    public function testHighlightWithSpecialCharactersInKey()
    {
        $this->assertEquals('<span class="highlight">t(e)st</span>', Util::highlight('t(e)st', 't(e)st'));
        $this->assertEquals('<span class="highlight">test/test</span>', Util::highlight('test/test', 'test/test'));
    }
}
