<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestCaseTest;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;

/**
 * @group test-reorder
 */
class ResultCacheExtensionTest extends TestCase
{
    /**
     * @var TestResultCache
     */
    protected $cache;

    /**
     * @var ResultCacheExtension
     */
    protected $extension;

    /**
     * @var TestResult
     */
    protected $result;

    protected function setUp(): void
    {
        $this->cache     = new TestResultCache;
        $this->extension = new ResultCacheExtension($this->cache);

        $listener = new TestListenerAdapter;
        $listener->add($this->extension);

        $this->result   = new TestResult;
        $this->result->addListener($listener);
    }

    /**
     * @dataProvider longTestNamesDataprovider
     */
    public function testStripsDataproviderParametersFromTestName(string $testName, string $expectedTestName): void
    {
        $test = new TestCaseTest($testName);
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_ERROR, $this->cache->getState($expectedTestName));
    }

    public function longTestNamesDataprovider(): array
    {
        return [
            'ClassName::testMethod' => [
                TestCaseTest::class . '::testSomething',
                'testSomething'],
            'ClassName::testMethod and data set number and vardump' => [
                TestCaseTest::class . '::testMethod with data set #123 (\'a\', "A", 0, false)',
                'testMethod with data set #123'],
            'ClassName::testMethod and data set name and vardump' => [
                TestCaseTest::class . '::testMethod with data set "data name" (\'a\', "A\", 0, false)',
                'testMethod with data set "data name"'],
        ];
    }

    public function testError(): void
    {
        $test = new \TestError('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_ERROR, $this->cache->getState('test_name'));
    }

    public function testFailure(): void
    {
        $test = new \Failure('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_FAILURE, $this->cache->getState('test_name'));
    }

    public function testSkipped(): void
    {
        $test = new \TestSkipped('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $this->cache->getState('test_name'));
    }

    public function testIncomplete(): void
    {
        $test = new \TestIncomplete('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_INCOMPLETE, $this->cache->getState('test_name'));
    }

    public function testPassedTestsOnlyCacheTime(): void
    {
        $test = new \Success('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $this->cache->getState('test_name'));
    }

    public function testWarning(): void
    {
        $test = new \TestWarning('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_WARNING, $this->cache->getState('test_name'));
    }

    public function testRisky(): void
    {
        $test = new \TestRisky('test_name');
        $test->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_RISKY, $this->cache->getState('test_name'));
    }

    public function testEmptySuite(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\EmptyTestCaseTest::class);
        $suite->run($this->result);

        $this->assertSame(BaseTestRunner::STATUS_WARNING, $this->cache->getState('Warning'));
    }
}
