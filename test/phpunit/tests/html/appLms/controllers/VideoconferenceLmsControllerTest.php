<?php

use PHPUnit\Framework\TestCase;

define('IN_FORMA', true);

// Setup Request mock to avoid SessionNotFoundException
require_once __DIR__ . '/../../../../../../html/vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

$request = Request::createFromGlobals();
$request->setSession(new Session(new MockArraySessionStorage()));
Request::setFactory(function () use ($request) {
    return $request;
});

require_once __DIR__ . '/../../../../../../html/lib/mvc/lib.controller.php';
require_once __DIR__ . '/../../../../../../html/lib/mvc/lib.lmscontroller.php';
require_once __DIR__ . '/../../../../../../html/lib/lib.utils.php';
require_once __DIR__ . '/../../../../../../html/lib/lib.yuilib.php';
require_once __DIR__ . '/../../../../../../html/appLms/controllers/VideoconferenceLmsController.php';

// Stub out dependencies inside the test class to avoid instantiating the real classes
class MockVideoconferenceLmsController extends VideoconferenceLmsController {
    public function init() {
        // override to prevent Docebo::user()->getIdSt() and UpdatesLms() calls
    }

    public function render($view_name, $data_for_view = false, $return = false, $morePaths = []) {
        $this->renderedView = $view_name;
        $this->renderedData = $data_for_view;
    }

    public $renderedView;
    public $renderedData;
}


class VideoconferenceLmsControllerTest extends TestCase
{
    public function testShowTask()
    {
        $controller = new MockVideoconferenceLmsController();
        $controller->showTask();

        $this->assertEquals('_tabs', $controller->renderedView);
        $this->assertEquals([], $controller->renderedData);
    }
}
