<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Thrift.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Till Klampaeckel <till@php.net>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class Framework_FailureTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup a simple TException and confirm the buffer is correct.
     *
     * @return void
     */
    public function testTExceptionFailure()
    {
        $texception = new TException(
            array(array('var' => 'errorCode'), array('var' => 'parameter'),),
            array('errorCode' => 10, 'parameter' => 'Note.name',)
        );

        $buffer = PHPUnit_Framework_TestFailure::exceptionToString($texception);

        $this->assertStringEqualsFile(dirname(__DIR__) . '/_files/ThriftException.txt', $buffer);
    }
}
