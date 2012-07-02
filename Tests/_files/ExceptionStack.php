<?php 
class ExceptionStackTestException extends Exception { }

class ExceptionStackTest extends PHPUnit_Framework_TestCase
{
    public function testAssertArrayEqualsArray()
    {
        try {
            $this->assertEquals(array(1), array(2), 'message');
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {            
            $comp = $e->getComparisonFailure();       
            $msg = $e->getMessage();
            if ($comp) {
                $msg = "$msg\n" . $comp->getDiff();
            }
            $newe = new ExceptionStackTestException("Child exception\n$msg", 101, $e);
            //$newe = new ExceptionStackTestException("Child exception\n$msg", 101);            
            throw $newe;         
        }
    }    

}
