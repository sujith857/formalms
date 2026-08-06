<?php

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}
if (!defined('_base_')) {
    define('_base_', realpath(__DIR__ . '/../../../../../html'));
}

require_once _base_ . '/lib/mvc/lib.controller.php';
require_once _base_ . '/lib/mvc/lib.lmscontroller.php';
require_once _base_ . '/appLms/controllers/PresenceLmsController.php';

// Mock getLogUserId
if (!function_exists('getLogUserId')) {
    function getLogUserId() { return 123; }
}

if (!class_exists('YuiLib')) {
    class YuiLib {
        public static function load() {}
    }
}

if (!class_exists('Format')) {
    class Format {
        public static function date($date, $type) { return 'formatted_date'; }
    }
}

class TestPresenceLmsController extends PresenceLmsController {
    public $renderedView = null;
    public $renderedParams = null;
    public $id_date_val = null;

    public function __construct() {
        // Do nothing in constructor
    }

    public function setModel($model) {
        $this->model = $model;
    }

    public function setSession($session) {
        $this->session = $session;
    }

    public function setIdDate($id_date) {
        $this->id_date = $id_date;
        $this->id_date_val = $id_date;
    }

    public function render($view_name, $data_for_view = false, $return = false, $morePaths = []) {
        $this->renderedView = $view_name;
        $this->renderedParams = $data_for_view;
        return '';
    }
}

class PresenceLmsControllerTest extends PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
    }

    public function testPresenceTaskWithIdDateZero()
    {
        $controller = new TestPresenceLmsController();

        $sessionMock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get'])
            ->getMock();
        $sessionMock->expects($this->any())
            ->method('get')
            ->with('idCourse')
            ->willReturn(456);
        $controller->setSession($sessionMock);

        $modelMock = $this->getMockBuilder(stdClass::class)
            ->setMethods([
                'getUserDateForCourse',
                'getDateInfoForPublicPresence',
                'setIdDate',
                'savePresence',
                'getPresenceTable',
                'getTestType'
            ])
            ->getMock();

        $modelMock->expects($this->once())
            ->method('getUserDateForCourse')
            ->with(123, 456)
            ->willReturn(['some_user_date']);

        $modelMock->expects($this->once())
            ->method('getDateInfoForPublicPresence')
            ->with(['some_user_date'])
            ->willReturn([]); // Empty date info

        $modelMock->expects($this->once())
            ->method('setIdDate')
            ->with(0);

        $controller->setModel($modelMock);
        $controller->setIdDate(0);

        // Call the function
        $controller->presenceTask();

        $this->assertEquals('presence_empty', $controller->renderedView);
        $this->assertEquals([], $controller->renderedParams);
    }

    public function testPresenceTaskWithIdDateZeroButHasDateInfo()
    {
        $controller = new TestPresenceLmsController();

        $sessionMock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get'])
            ->getMock();
        $sessionMock->expects($this->any())
            ->method('get')
            ->with('idCourse')
            ->willReturn(456);
        $controller->setSession($sessionMock);

        $modelMock = $this->getMockBuilder(stdClass::class)
            ->setMethods([
                'getUserDateForCourse',
                'getDateInfoForPublicPresence',
                'setIdDate',
                'savePresence',
                'getPresenceTable',
                'getTestType'
            ])
            ->getMock();

        $modelMock->expects($this->once())
            ->method('getUserDateForCourse')
            ->with(123, 456)
            ->willReturn(['some_user_date']);

        $dateInfo = [
            ['id_date' => 789, 'code' => 'D01', 'name' => 'First Date', 'date_begin' => '2023-01-01']
        ];

        $modelMock->expects($this->once())
            ->method('getDateInfoForPublicPresence')
            ->with(['some_user_date'])
            ->willReturn($dateInfo);

        $modelMock->expects($this->once())
            ->method('setIdDate')
            ->with(789);

        $modelMock->expects($this->once())
            ->method('getPresenceTable')
            ->willReturn('html_table_content');

        $modelMock->expects($this->once())
            ->method('getTestType')
            ->willReturn('some_test_type');

        $controller->setModel($modelMock);
        $controller->setIdDate(0);

        // Call the function
        $controller->presenceTask();

        $this->assertEquals('presence', $controller->renderedView);

        $expectedDropdown = [
            789 => 'D01 - First Date (formatted_date)'
        ];

        $this->assertEquals($modelMock, $controller->renderedParams['model']);
        $this->assertEquals($expectedDropdown, $controller->renderedParams['dropdown']);
        $this->assertEquals('html_table_content', $controller->renderedParams['tb']);
        $this->assertEquals('some_test_type', $controller->renderedParams['test_type']);
        $this->assertEquals($expectedDropdown, $controller->renderedParams['date_for_dropdown']);
    }

    public function testPresenceTaskWithSavePost()
    {
        $controller = new TestPresenceLmsController();

        $sessionMock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get'])
            ->getMock();
        $sessionMock->expects($this->any())
            ->method('get')
            ->with('idCourse')
            ->willReturn(456);
        $controller->setSession($sessionMock);

        $modelMock = $this->getMockBuilder(stdClass::class)
            ->setMethods([
                'getUserDateForCourse',
                'getDateInfoForPublicPresence',
                'setIdDate',
                'savePresence',
                'getPresenceTable',
                'getTestType'
            ])
            ->getMock();

        $modelMock->expects($this->once())
            ->method('getUserDateForCourse')
            ->willReturn(['some_user_date']);

        $dateInfo = [
            ['id_date' => 789, 'code' => 'D01', 'name' => 'First Date', 'date_begin' => '2023-01-01']
        ];

        $modelMock->expects($this->once())
            ->method('getDateInfoForPublicPresence')
            ->willReturn($dateInfo);

        $modelMock->expects($this->once())
            ->method('setIdDate')
            ->with(789);

        // Test that savePresence is called when $_POST['save'] is set
        $modelMock->expects($this->once())
            ->method('savePresence');

        $modelMock->expects($this->once())
            ->method('getPresenceTable')
            ->willReturn('html_table_content');

        $modelMock->expects($this->once())
            ->method('getTestType')
            ->willReturn('some_test_type');

        $controller->setModel($modelMock);
        $controller->setIdDate(789);

        // Set POST variable
        $_POST['save'] = 'true';

        // Call the function
        $controller->presenceTask();

        $this->assertEquals('presence', $controller->renderedView);

        // Clean up
        unset($_POST['save']);
    }
}
