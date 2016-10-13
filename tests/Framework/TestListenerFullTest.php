<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @covers     PHPUnit_Framework_TestListener
 */
class Framework_TestListenerFullTest extends PHPUnit_Framework_TestCase
{
    /**
     * Creates Mocked TestListener with expected methods 'startTest' and 'endTest' already set
     * 
     * @param PHPUnit_Framework_Test $test
     * @return PHPUnit_Framework_TestListener
     */
    protected function buildDefaultListener(PHPUnit_Framework_Test $test)
    {
        $listener = $this->getMockBuilder(PHPUnit_Framework_TestListener::class)->getMock();
        
        $listener->expects($this->once())
                 ->method('startTest')
                 ->with($this->identicalTo($test));
        $listener->expects($this->once())
                 ->method('endTest')
                 ->with($this->identicalTo($test));
        return $listener;
    }
    
    /**
     * 
     * @param PHPUnit_Framework_Test $test
     * @param string $expectedMethod
     */
    protected function doGenericStateTest(PHPUnit_Framework_Test $test, $expectedMethod)
    {
        $listener = $this->buildDefaultListener($test);
        
        $listener->expects($this->once())
                 ->method($expectedMethod);
        
        $result = new PHPUnit_Framework_TestResult;
        $result->addListener($listener);
        $test->run($result);
    }

    /**
     * @covers PHPUnit_Framework_TestListener::startTest
     * @covers PHPUnit_Framework_TestListener::endTest
     */
    public function testSuccess()
    {
        $test = new Success;
        $listener = $this->buildDefaultListener($test);
        
        $listener->expects($this->never())
                 ->method('addFailure');
        $listener->expects($this->never())
                 ->method('addError');
        
        $result = new PHPUnit_Framework_TestResult;
        $result->addListener($listener);
        $test->run($result);
    }
    
    /**
     * @covers PHPUnit_Framework_TestListener::addError
     */
    public function testError()
    {
        $test = new TestError;
        $this->doGenericStateTest($test, 'addError');
    }

    /**
     * @covers PHPUnit_Framework_TestListener::addFailure
     */
    public function testFailure()
    {
        $test = new Failure;
        $this->doGenericStateTest($test, 'addFailure');
    }
    
    /**
     * @covers PHPUnit_Framework_TestListener::addIncompleteTest
     */
    public function testIncomplete()
    {
        $test = new TestIncomplete;
        $this->doGenericStateTest($test, 'addIncompleteTest');
    }
    
    /**
     * @covers PHPUnit_Framework_TestListener::addRiskyTest
     */
    /*public function testRisky()
    {
        $test = new Risky;  // TODO add risky test class
        $this->doGenericStateTest($test, 'addRiskyTest');
    }*/
    
    /**
     * @covers PHPUnit_Framework_TestListener::addSkippedTest
     */
    public function testSkipped()
    {
        $test = new TestSkipped;
        $this->doGenericStateTest($test, 'addSkippedTest');
    }
    
    /**
     * @covers PHPUnit_Framework_TestListener::startTestSuite
     * @covers PHPUnit_Framework_TestListener::endTestSuite
     */
    public function testSuite()
    {
        $suite = new PHPUnit_Framework_TestSuite;
        $tests = [
                new Success,
                new TestSkipped,
                new Failure
            ];
        $callbackArgs = [];
        
        foreach ($tests as $test) {
            $suite->addTest($test);
            $callbackArgs[] = [$this->equalTo($test)];
        }
        
        $listener = $this->getMockBuilder(PHPUnit_Framework_TestListener::class)->getMock();
        
        $listener->expects($this->exactly(count($tests)))
                 ->method('startTest')
                 ->withConsecutive(...$callbackArgs);
        $listener->expects($this->exactly(count($tests)))
                 ->method('endTest')
                 ->withConsecutive(...$callbackArgs);
        
        $listener->expects($this->once())
                 ->method('addSkippedTest')
                 ->with($tests[1]);
        $listener->expects($this->once())
                 ->method('addFailure')
                 ->with($tests[2]);
        
        $listener->expects($this->once())
                 ->method('startTestSuite')
                 ->with($this->identicalTo($suite));
        $listener->expects($this->once())
                 ->method('endTestSuite')
                 ->with($this->identicalTo($suite));
        
        $result = new PHPUnit_Framework_TestResult;
        $result->addListener($listener);
        $suite->run($result);
    }
    
    public function testDependency()
    {
        $suite = new PHPUnit_Framework_TestSuite;
        $suite->addTestFile(__DIR__ . '/../_files/DependencySuccessTest.php');
        
        $testCount = 0;
        $suiteCount = 0;
        $tests = [];
        $callbackArgs = [];
        
        foreach ($suite as $sub_suite) {
            $suiteCount++;
            foreach ($sub_suite as $test) {
                $testCount++;
                $tests[] = $test;
                $callbackArgs[] = [$this->equalTo($test)];
            }
        }
        
        $this->assertEquals(1, $suiteCount);
        $this->assertEquals(3, $testCount);
        
        $listener = $this->getMockBuilder(PHPUnit_Framework_TestListener::class)->getMock();
        
        $listener->expects($this->exactly(count($tests)))
                 ->method('startTest')
                 ->withConsecutive(...$callbackArgs);
        $listener->expects($this->exactly(count($tests)))
                 ->method('endTest')
                 ->withConsecutive(...$callbackArgs);
        
        $listener->expects($this->never())
                 ->method('addFailure');
        $listener->expects($this->never())
                 ->method('addSkippedTest');
        
        $result = new PHPUnit_Framework_TestResult;
        $result->addListener($listener);
        $suite->run($result);
    }
    
    /**
     * @covers PHPUnit_Framework_TestListener::addSkippedTest
     */
    public function testSkippedByDependency()
    {
        $suite = new PHPUnit_Framework_TestSuite;
        $suite->addTestFile(__DIR__ . '/../_files/DependencyFailureTest.php');
        
        $testCount = 0;
        $suiteCount = 0;
        $tests = [];
        $callbackArgs = [];
        
        foreach ($suite as $sub_suite) {
            $suiteCount++;
            foreach ($sub_suite as $test) {
                $testCount++;
                $tests[] = $test;
                $callbackArgs[] = [$this->equalTo($test)];
            }
        }
        
        $this->assertEquals(1, $suiteCount);
        $this->assertEquals(4, $testCount);
        
        $listener = $this->getMockBuilder(PHPUnit_Framework_TestListener::class)->getMock();
        
        $listener->expects($this->exactly(count($tests)))
                 ->method('startTest')
                 ->withConsecutive(...$callbackArgs);
        $listener->expects($this->exactly(count($tests)))
                 ->method('endTest')
                 ->withConsecutive(...$callbackArgs);
        
        $listener->expects($this->once())
                 ->method('addFailure');
        $listener->expects($this->exactly(3))
                 ->method('addSkippedTest');
        
        $result = new PHPUnit_Framework_TestResult;
        $result->addListener($listener);
        $suite->run($result);
    }
}
