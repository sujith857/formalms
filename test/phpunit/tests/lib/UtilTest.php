<?php

if (!defined('IN_FORMA')) define('IN_FORMA', true);

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    private $originalRequestMethod;

    protected function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../html/lib/lib.utils.php';

        if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $this->originalRequestMethod = $_SERVER['REQUEST_METHOD'];
        } else {
            $this->originalRequestMethod = null;
        }
    }

    protected function tearDown()
    {
        if ($this->originalRequestMethod !== null) {
            $_SERVER['REQUEST_METHOD'] = $this->originalRequestMethod;
        } else {
            unset($_SERVER['REQUEST_METHOD']);
        }

        parent::tearDown();
    }

    public function testRequestIsPostReturnsTrueWhenMethodIsPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(Util::requestIsPost());
    }

    public function testRequestIsPostReturnsTrueWhenMethodIsPostLowercase()
    {
        $_SERVER['REQUEST_METHOD'] = 'post';
        $this->assertTrue(Util::requestIsPost());
    }

    public function testRequestIsPostReturnsFalseWhenMethodIsGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(Util::requestIsPost());
    }

    public function testRequestIsPostReturnsFalseWhenMethodIsNotSet()
    {
        unset($_SERVER['REQUEST_METHOD']);
        $this->assertFalse(@Util::requestIsPost());
    }
}
