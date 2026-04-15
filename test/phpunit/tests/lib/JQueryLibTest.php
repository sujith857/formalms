<?php

use PHPUnit\Framework\TestCase;

class JQueryLibTest extends TestCase
{
    protected function setUp()
    {
        if (!defined('IN_FORMA')) {
            define('IN_FORMA', true);
        }
        if (!defined('_base_')) {
            define('_base_', '/base/');
        }
        if (!defined('_deeppath_')) {
            define('_deeppath_', '/');
        }

        require_once dirname(__DIR__, 4) . '/html/lib/Get.php';
        require_once dirname(__DIR__, 4) . '/html/lib/lib.utils.php';
        require_once dirname(__DIR__, 4) . '/html/lib/lib.jquerylib.php';
    }

    public function testLoadBootstrap()
    {
        $result = \JQueryLib::loadBootstrap();

        $this->assertContains('bootstrap-3.3.6.js', $result);
        $this->assertContains('bootstrap-3.3.6.css', $result);
        $this->assertContains('bootstrap-theme-3.3.6.css', $result);
    }

    public function testLoadBootstrapWithVersion()
    {
        $result = \JQueryLib::loadBootstrap('-min');

        $this->assertContains('bootstrap-3.3.6-min.js', $result);
        $this->assertContains('bootstrap-3.3.6-min.css', $result);
        $this->assertContains('bootstrap-theme-3.3.6-min.css', $result);
    }
}
