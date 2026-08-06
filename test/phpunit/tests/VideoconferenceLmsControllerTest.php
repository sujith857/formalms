<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

// Mock globals needed by core code
if (!isset($GLOBALS['prefix_scs'])) $GLOBALS['prefix_scs'] = 'prefix_';

require_once 'html/vendor/autoload.php';

// Instead of polluting the global namespace for other tests,
// we create a custom mock subclass of VideoconferenceLmsController
// and override the init method to be testable without globals if possible.
// Wait, the issue is that init() itself is what we want to test, and it calls
// YuiLib::load, Lang::init, new VideoconferenceLms, new UpdatesLms.

// If the codebase doesn't have an autoloader that will load these,
// we have to provide them. Let's wrap them in a namespace so they don't
// pollute the global space? But the core code calls them from the global space!

// The standard way to mock global classes in a legacy test is to define them
// in a bootstrap or ensure this test runs in isolation.
// If process isolation is broken due to PHPUnit 6.5 bug (Bad file descriptor),
// the next best thing is to use an autoloader that only defines the classes
// if they haven't been defined by another test that loaded the real ones.

spl_autoload_register(function ($class) {
    // Only mock specific legacy global classes if they are not already defined
    $mocks = [
        'Docebo' => 'class Docebo { public static function user() { return new class { public function getIdSt() { return 1; } }; } }',
        'Lang' => 'class Lang { public static function init($module) {} }',
        'YuiLib' => 'class YuiLib { public static function load($libs) {} }',
        'VideoconferenceLms' => 'class VideoconferenceLms { public function __construct($userId) {} }',
        'UpdatesLms' => 'class UpdatesLms { public function videoconferenceCounterUpdates() { return ["live" => 1, "planned" => 0, "history" => 5]; } }',
    ];

    if (isset($mocks[$class]) && !class_exists($class, false)) {
        eval($mocks[$class]);
    }
});

require_once 'html/lib/mvc/lib.controller.php';
require_once 'html/lib/mvc/lib.lmscontroller.php';
require_once 'html/appLms/controllers/VideoconferenceLmsController.php';

class VideoconferenceLmsControllerTest extends TestCase {

    public function setUp() {
        parent::setUp();

        $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
        FormaLms\lib\Request\RequestManager::getInstance()->setSession($session);
    }

    public function testInit() {
        $controller = new VideoconferenceLmsController();
        $controller->init();

        $this->assertInstanceOf('VideoconferenceLms', $controller->model);
        $this->assertEquals(['live' => 1, 'planned' => 0, 'history' => 5], $controller->info);
    }
}
