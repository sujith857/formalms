<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}
if (!defined('_lib_')) {
    define('_lib_', 'html/lib');
}
if (!defined('_base_')) {
    define('_base_', 'html');
}
if (!defined('_lms_')) {
    define('_lms_', 'html/appLms');
}

require_once 'html/vendor/autoload.php';

// Avoid declaring classes if they are already declared
if (!class_exists('Forma')) {
    class Forma {
        public static function inc($path) {
            return $path;
        }
    }
}

// We need to initialize the session manager correctly so that Model doesn't fail
$session = new Session(new MockArraySessionStorage());
$reflection = new \ReflectionClass(FormaLms\lib\Session\SessionManager::getInstance());
$property = $reflection->getProperty('session');
$property->setAccessible(true);
$property->setValue(FormaLms\lib\Session\SessionManager::getInstance(), $session);

require_once 'html/lib/mvc/lib.controller.php';
require_once 'html/lib/mvc/lib.lmscontroller.php';
require_once 'html/lib/mvc/lib.model.php';
require_once 'html/appLms/models/MycoursesLms.php';
require_once 'html/lib/Helpers/Filters/FilterManager.php';
require_once 'html/lib/Helpers/Filters/Course/FilterCourseManager.php';
require_once 'html/appLms/controllers/MycoursesLmsController.php';

class MycoursesLmsControllerTest extends TestCase
{
    protected function setUp()
    {
        $session = new Session(new MockArraySessionStorage());
        FormaLms\lib\Request\RequestManager::getInstance()->setSession($session);
    }

    public function testInit()
    {
        $controller = new MycoursesLmsController();
        $controller->init(); // explicitly call init

        $reflection = new \ReflectionClass($controller);

        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);
        $this->assertInstanceOf('MycoursesLms', $modelProperty->getValue($controller));

        $filterManagerProperty = $reflection->getProperty('filterManager');
        $filterManagerProperty->setAccessible(true);
        $this->assertInstanceOf('FilterCourseManager', $filterManagerProperty->getValue($controller));
    }
}
