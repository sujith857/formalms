<?php

use PHPUnit\Framework\TestCase;

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

require_once __DIR__ . '/../../../../../html/lib/mvc/lib.controller.php';
require_once __DIR__ . '/../../../../../html/lib/mvc/lib.lmscontroller.php';
require_once __DIR__ . '/../../../../../html/appLms/controllers/VideoconferenceLmsController.php';

class VideoconferenceLmsControllerTest extends TestCase
{
    public function testPlanned()
    {
        // Define a mock class to replace VideoconferenceLmsController and override the getPlannedTable and render methods
        $controllerMock = $this->getMockBuilder('VideoconferenceLmsController')
            ->disableOriginalConstructor()
            ->onlyMethods(['getPlannedTable', 'render'])
            ->getMock();

        $expectedTable = 'mock_table';

        $controllerMock->expects($this->once())
            ->method('getPlannedTable')
            ->willReturn($expectedTable);

        $controllerMock->expects($this->once())
            ->method('render')
            ->with('conference', ['tb' => $expectedTable]);

        $controllerMock->planned();
    }
}
