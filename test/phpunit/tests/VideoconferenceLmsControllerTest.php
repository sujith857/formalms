<?php
use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) define('IN_FORMA', true);
if (!defined('Docebo')) define('Docebo', true);
if (!defined('_deeppath_')) define('_deeppath_', '');
if (!defined('_base_')) define('_base_', realpath(__DIR__ . '/../../../html'));
if (!defined('_folder_base_')) define('_folder_base_', 'html');

require_once _base_ . '/lib/mvc/lib.controller.php';
require_once _base_ . '/lib/mvc/lib.lmscontroller.php';
require_once _base_ . '/appLms/controllers/VideoconferenceLmsController.php';

class VideoconferenceLmsControllerTestProxy extends VideoconferenceLmsController {
    public $historyTableCalled = false;
    public $renderCalled = false;
    public $renderArgs = [];

    public function __construct() {
        // bypass parent constructor that uses session/request
    }

    public function getHistoryTable() {
        $this->historyTableCalled = true;
        return 'dummy_table';
    }

    public function render($view_name, $data_for_view = false, $return = false, $morePaths = []) {
        $this->renderCalled = true;
        $this->renderArgs = func_get_args();
    }
}

class VideoconferenceLmsControllerTest extends TestCase {
    public function testHistory() {
        $controller = new VideoconferenceLmsControllerTestProxy();

        $controller->history();

        $this->assertTrue($controller->historyTableCalled, "getHistoryTable should be called");
        $this->assertTrue($controller->renderCalled, "render should be called");
        $this->assertEquals('conference', $controller->renderArgs[0], "First argument to render should be 'conference'");
        $this->assertEquals(['tb' => 'dummy_table'], $controller->renderArgs[1], "Second argument to render should be the table data");
    }
}
