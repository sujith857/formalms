<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}
if (!defined('Docebo')) {
    define('Docebo', true);
}

// Ensure base path is set to html folder
if (!defined('_base_')) {
    define('_base_', realpath(__DIR__ . '/../../../../../html'));
}

if (!defined('_lib_')) {
    define('_lib_', _base_ . '/lib');
}

// Needed to run without complete framework setup
if (!class_exists('Forma')) {
    class Forma {
        public static function inc($path) {
            return $path;
        }
    }
}

// Mock MycoursesLms Model
if (!class_exists('MycoursesLms')) {
    class MycoursesLms {}
}

require_once _base_ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

$session = new Session(new MockArraySessionStorage());

require_once _base_ . '/lib/Request/RequestManager.php';
\FormaLms\lib\Request\RequestManager::getInstance()->setSession($session);

require_once _base_ . '/lib/mvc/lib.controller.php';
require_once _base_ . '/lib/mvc/lib.lmscontroller.php';
require_once _base_ . '/appLms/controllers/MycoursesLmsController.php';

use PHPUnit\Framework\TestCase;

class MycoursesLmsControllerTest extends TestCase
{
    public function testInit()
    {
        $controller = new MycoursesLmsController();
        // init is called in the constructor of Controller
        // $controller->init();

        $reflection = new ReflectionClass($controller);

        $modelProp = $reflection->getProperty('model');
        $modelProp->setAccessible(true);
        $model = $modelProp->getValue($controller);

        $filterManagerProp = $reflection->getProperty('filterManager');
        $filterManagerProp->setAccessible(true);
        $filterManager = $filterManagerProp->getValue($controller);

        $this->assertInstanceOf('MycoursesLms', $model);
        $this->assertInstanceOf('FilterCourseManager', $filterManager);
    }
}
