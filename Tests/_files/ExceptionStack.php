<?php 
class ExceptionStackTestException extends Exception { }

class ExceptionStackTest extends PHPUnit_Framework_TestCase
{
    public function testPrintingChildException()
    {
        try {
            $this->assertEquals(array(1), array(2), 'message');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $message = $e->getMessage() . "\n" . $e->getComparisonFailure()->getDiff();
            throw new ExceptionStackTestException("Child exception\n$message", 101, $e);
        }
    }

    public function testNestedExceptions()
    {
        $exceptionThree = new Exception('Three');
        $exceptionTwo = new InvalidArgumentException('Two', 0, $exceptionThree);
        $exceptionOne = new Exception('One', 0, $exceptionTwo);
        throw $exceptionOne;
    }
}
