<?php

define('IN_FORMA', true);
if (!defined('_base_')) {
    define('_base_', realpath(__DIR__ . '/../../../../../html'));
}
if (!defined('_folder_base_')) {
    define('_folder_base_', 'html');
}
if (!defined('_deeppath_')) {
    define('_deeppath_', '');
}

require_once 'html/lib/Get.php';
require_once 'html/lib/mvc/lib.controller.php';
require_once 'html/lib/mvc/lib.lmscontroller.php';
require_once 'html/appLms/controllers/VideoconferenceLmsController.php';

if (!class_exists('Lang')) {
    class Lang {
        public static function t($key, $namespace = null) {
            return $key;
        }
        public static function init($namespace) {
            // Do nothing
        }
    }
}

if (!class_exists('Format')) {
    class Format {
        public static function date($date, $type) {
            return $date; // Format::date actually takes formatted string in controller, see controller: Format::date(date('Y-m-d H:i:s', $conference_info['starttime']), 'datetime')
        }
    }
}

class VideoconferenceLmsControllerTest extends PHPUnit\Framework\TestCase {
    public function testGetHistoryTable() {
        if (method_exists($this->getMockBuilder('VideoconferenceLmsController'), 'onlyMethods')) {
            $controller = $this->getMockBuilder('VideoconferenceLmsController')
                ->disableOriginalConstructor()
                ->onlyMethods(['render'])
                ->getMock();

            $mockModel = $this->getMockBuilder('stdClass')
                ->addMethods(['getHistoryConference', 'getCourseName'])
                ->getMock();
        } else {
            $controller = $this->getMockBuilder('VideoconferenceLmsController')
                ->disableOriginalConstructor()
                ->setMethods(['render'])
                ->getMock();

            $mockModel = $this->getMockBuilder('stdClass')
                ->setMethods(['getHistoryConference', 'getCourseName'])
                ->getMock();
        }

        $mockModel->expects($this->once())
            ->method('getHistoryConference')
            ->willReturn([
                [
                    'name' => 'Test Conf',
                    'room_type' => 'test_type',
                    'idCourse' => 123,
                    'starttime' => strtotime('2023-01-01 10:00:00'),
                    'endtime' => strtotime('2023-01-01 11:00:00'),
                    'meetinghours' => 1,
                    'maxparticipants' => 10
                ]
            ]);

        $mockModel->expects($this->once())
            ->method('getCourseName')
            ->willReturn([
                123 => 'Test Course'
            ]);

        $controller->model = $mockModel;

        $table = $controller->getHistoryTable();

        $this->assertInstanceOf('Table', $table);
        $this->assertEquals(Lang::t('_HISTORY', 'course'), $table->table_caption);
        $this->assertEquals(Lang::t('_ACTIVE', 'course'), $table->table_summary);

        // Assert headers - inside table->table_head
        // Note that TableRow has cells, TableCell has label
        $this->assertNotEmpty($table->table_head);

        // Let's grab first row in table_head
        $headRow = reset($table->table_head);
        $this->assertInstanceOf('TableRow', $headRow);

        $this->assertEquals(Lang::t('_VIDEOCONFERENCE', 'course'), $headRow->cells[0]->label);
        $this->assertEquals(Lang::t('_MAX_PARTICIPANTS', 'conference'), $headRow->cells[6]->label);

        // Assert body
        $this->assertNotEmpty($table->table_body);
        $bodyRow = reset($table->table_body);
        $this->assertInstanceOf('TableRow', $bodyRow);

        $this->assertEquals('Test Conf', $bodyRow->cells[0]->label);
        $this->assertEquals('test_type', $bodyRow->cells[1]->label);
        $this->assertEquals('Test Course', $bodyRow->cells[2]->label);
        // Format::date output mock will output whatever date(...) gave it
        $this->assertEquals(date('Y-m-d H:i:s', strtotime('2023-01-01 10:00:00')), $bodyRow->cells[3]->label);
        $this->assertEquals(date('Y-m-d H:i:s', strtotime('2023-01-01 11:00:00')), $bodyRow->cells[4]->label);
        $this->assertEquals(1, $bodyRow->cells[5]->label);
        $this->assertEquals(10, $bodyRow->cells[6]->label);
    }
}
