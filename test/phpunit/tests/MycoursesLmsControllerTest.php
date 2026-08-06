<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}
if (!defined('_base_')) {
    define('_base_', realpath(__DIR__ . '/../../..') . '/html');
}
if (!defined('_lib_')) {
    define('_lib_', _base_ . '/lib');
}
if (!defined('_lms_')) {
    define('_lms_', _base_ . '/appLms');
}

if (!class_exists('Forma')) {
    class Forma {
        public static function inc($path) {
            return $path;
        }
    }
}

require_once _lib_ . '/mvc/lib.controller.php';
require_once _lib_ . '/mvc/lib.lmscontroller.php';
require_once _lib_ . '/mvc/lib.model.php';
require_once _lms_ . '/models/MycoursesLms.php';
require_once _lms_ . '/controllers/MycoursesLmsController.php';

class MockMycoursesLms extends MycoursesLms {
    public $shouldRedirectToCatalogueResult = false;
    public $getCatalogueURLResult = '';
    public $getMyCoursesURLResult = '';

    public function __construct() {}

    public function shouldRedirectToCatalogue() {
        return $this->shouldRedirectToCatalogueResult;
    }

    public function getCatalogueURL() {
        return $this->getCatalogueURLResult;
    }

    public function getMyCoursesURL() {
        return $this->getMyCoursesURLResult;
    }
}

class MockMycoursesLmsController extends MycoursesLmsController {
    public function __construct() {}
}

class MycoursesLmsControllerTest extends TestCase
{
    private $controller;
    private $modelMock;

    public function setUp(): void
    {
        $this->controller = new MockMycoursesLmsController();
        $this->modelMock = new MockMycoursesLms();

        $reflection = new \ReflectionClass('MycoursesLmsController');
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);
        $modelProperty->setValue($this->controller, $this->modelMock);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHomeRedirectsToCatalogueWhenTrue()
    {
        // Define dummy Util inside the separate process
        if (!class_exists('Util', false)) {
            eval('
                class Util {
                    public static function jump_to($url, $anchor = "") {
                        throw new \Exception("JUMP_TO:" . $url);
                    }
                }
            ');
        }

        $this->modelMock->shouldRedirectToCatalogueResult = true;
        $this->modelMock->getCatalogueURLResult = '/catalogue';

        try {
            $this->controller->home();
            $this->fail("Expected Exception with JUMP_TO URL was not thrown");
        } catch (\Exception $e) {
            $this->assertEquals("JUMP_TO:/catalogue", $e->getMessage());
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHomeRedirectsToMyCoursesWhenFalse()
    {
        // Define dummy Util inside the separate process
        if (!class_exists('Util', false)) {
            eval('
                class Util {
                    public static function jump_to($url, $anchor = "") {
                        throw new \Exception("JUMP_TO:" . $url);
                    }
                }
            ');
        }

        $this->modelMock->shouldRedirectToCatalogueResult = false;
        $this->modelMock->getMyCoursesURLResult = '/mycourses';

        try {
            $this->controller->home();
            $this->fail("Expected Exception with JUMP_TO URL was not thrown");
        } catch (\Exception $e) {
            $this->assertEquals("JUMP_TO:/mycourses", $e->getMessage());
        }
    }
}
