<?php

use PHPUnit\Framework\TestCase;

class JQueryLibTest extends TestCase
{
    public function setUp()
    {
        if (!defined('IN_FORMA')) {
            define('IN_FORMA', true);
        }
        if (!defined('_deeppath_')) {
            define('_deeppath_', '');
        }
        if (!defined('_base_')) {
            define('_base_', dirname(__FILE__) . '/../../../../html');
        }
        if (!defined('_folder_base_')) {
            define('_folder_base_', 'html');
        }
        require_once dirname(__FILE__) . '/../../../../html/lib/lib.jquerylib.php';
        require_once dirname(__FILE__) . '/../../../../html/lib/lib.utils.php';
        require_once dirname(__FILE__) . '/../../../../html/lib/Get.php';
    }

    public function testLoadBootstrap()
    {
        $result = JQueryLib::loadBootstrap();

        $this->assertContains('bootstrap-3.3.6.js', $result);
        $this->assertContains('bootstrap-3.3.6.css', $result);
        $this->assertContains('bootstrap-theme-3.3.6.css', $result);

        $this->assertContains('<script', $result);
        $this->assertContains('<link', $result);
    }

    public function testLoadBootstrapWithVersionSuffix()
    {
        $result = JQueryLib::loadBootstrap('-custom');

        $this->assertContains('bootstrap-3.3.6-custom.js', $result);
        $this->assertContains('bootstrap-3.3.6-custom.css', $result);
        $this->assertContains('bootstrap-theme-3.3.6-custom.css', $result);
    }
}
