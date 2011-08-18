<?php

/**
 *
 *
 * @package    PHPUnit
 * @author     Victor Karamzin <admin@visor.ws>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 */
class Framework_AssertExceptionsTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Closure
     */
    private static $closure;

    public static function setUpBeforeClass()
    {
        self::$closure = function() { throw new RuntimeException('Test exception'); };
    }

    public function testAssertExceptionShouldPassWhenExceptionThrows()
    {
        try {
            self::assertException(self::$closure, 'RuntimeException');
        } catch (Exception $e) {
            self::fail('No exception should be here ' . PHP_EOL . $e);
        }
    }

    public function testAssertExceptionShouldPassWithExceptionStringWhenExceptionThrows()
    {
        try {
            self::assertException(self::$closure, 'RuntimeException', 'Test exception');
        } catch (Exception $e) {
            self::fail('No exception should be here ' . PHP_EOL . $e);
        }
    }

    public function testAssertExceptionShouldFailsWhenNoExceptionThrown()
    {
        $exception = null;
        try {
            self::assertException(function () {}, 'Exception');
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertInstanceOf('PHPUnit_Framework_AssertionFailedError', $exception);
        self::assertContains('no exception thrown', $exception->getMessage());
        self::assertContains('exception <Exception> should throw', $exception->getMessage());
    }

    public function testAssertExceptionShouldFailsWhenMessageDoesNotMatches()
    {
        $exception = null;
        try {
            self::assertException(self::$closure, 'RuntimeException', 'Another exception');
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf('PHPUnit_Framework_AssertionFailedError', $exception);
        self::assertContains('exception message not matches', $exception->getMessage());
        self::assertContains('exception <RuntimeException> with message \'Another exception\' should throw', $exception->getMessage());
    }

    public function testAssertExceptionShouldFailsWhenExceptionDoesNotMatches()
    {
        $exception = null;
        try {
            self::assertException(self::$closure, 'PHPUnit_Framework_Error');
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf('PHPUnit_Framework_AssertionFailedError', $exception);
        self::assertContains('exception class not matches', $exception->getMessage());
        self::assertContains('exception <PHPUnit_Framework_Error> should throw', $exception->getMessage());
    }

    public function testAssertNotExceptionShouldPassWhenNoExceptionThrown()
    {
        $exception = null;
        try {
            self::assertNotException(function() {}, 'RuntimeException');
        } catch (Exception $e) {
            $exception = $e;
        }
        self::assertNull($exception);
    }

    public function testAssertNotExceptionShouldFailsWhenExceptionThrows()
    {
        $exception = null;
        try {
            self::assertNotException(self::$closure);
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf('PHPUnit_Framework_AssertionFailedError', $exception);
        self::assertContains('no exception should throw', $exception->getMessage());
    }

    public static function tearDownAfterClass()
    {
        self::$closure = null;
    }

}