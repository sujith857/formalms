<?php

use PHPUnit\Framework\TestCase;

class MycoursesLmsControllerTest extends TestCase
{
    private $tmpFile;

    protected function setUp(): void
    {
        if (!defined('IN_FORMA')) define('IN_FORMA', true);
        if (!defined('_lib_')) define('_lib_', '');
        if (!defined('_base_')) define('_base_', '');
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInit()
    {
        if (!class_exists('Forma', false)) {
            // Create a temporary file to satisfy the require_once call securely
            $this->tmpFile = sys_get_temp_dir() . '/dummy_forma_inc_'.uniqid().'.php';
            file_put_contents($this->tmpFile, '<?php ');

            // Generate the class definition dynamically
            $code = 'class Forma { public static function inc($path) { return "'.$this->tmpFile.'"; } }';
            eval($code);
        }
        if (!class_exists('FilterCourseManager', false)) {
            eval('class FilterCourseManager {}');
        }
        if (!class_exists('MycoursesLms', false)) {
            eval('class MycoursesLms {}');
        }
        if (!class_exists('LmsController', false)) {
            eval('class LmsController {}');
        }

        require_once __DIR__ . '/../../../../../html/appLms/controllers/MycoursesLmsController.php';

        $controller = new \MycoursesLmsController();
        $controller->init();

        $refClass = new \ReflectionClass(\MycoursesLmsController::class);

        $modelProp = $refClass->getProperty('model');
        $modelProp->setAccessible(true);
        $this->assertInstanceOf('MycoursesLms', $modelProp->getValue($controller));

        $filterManagerProp = $refClass->getProperty('filterManager');
        $filterManagerProp->setAccessible(true);
        $this->assertInstanceOf('FilterCourseManager', $filterManagerProp->getValue($controller));
    }
}
