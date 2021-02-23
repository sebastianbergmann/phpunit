<?php declare(strict_types=1);
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
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\EmptyTestCaseTest;
use PHPUnit\TestFixture\Failure;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestFixture\TestError;
use PHPUnit\TestFixture\TestIncomplete;
use PHPUnit\TestFixture\TestRisky;
use PHPUnit\TestFixture\TestSkipped;
use PHPUnit\TestFixture\TestWarning;

/**
 * @group test-reorder
 * @small
 */
final class ResultCacheExtensionTest extends TestCase
{
    /**
     * @var DefaultTestResultCache
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
        $this->cache     = new DefaultTestResultCache;
        $this->extension = new ResultCacheExtension($this->cache);

        $listener = new TestListenerAdapter;
        $listener->add($this->extension);

        $this->result = new TestResult;
        $this->result->addListener($listener);
    }

    public function testError(): void
    {
        $test = new TestError('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(TestError::class . '::testOne')->isError());
    }

    public function testFailure(): void
    {
        $test = new Failure('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(Failure::class . '::testOne')->isFailure());
    }

    public function testSkipped(): void
    {
        $test = new TestSkipped('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(TestSkipped::class . '::testOne')->isSkipped());
    }

    public function testIncomplete(): void
    {
        $test = new TestIncomplete('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(TestIncomplete::class . '::testOne')->isIncomplete());
    }

    public function testPassedTestsOnlyCacheTime(): void
    {
        $test = new Success('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(Success::class . '::testOne')->isUnknown());
    }

    public function testWarning(): void
    {
        $test = new TestWarning('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(TestWarning::class . '::testOne')->isWarning());
    }

    public function testRisky(): void
    {
        $test = new TestRisky('testOne');
        $test->run($this->result);

        $this->assertTrue($this->cache->status(TestRisky::class . '::testOne')->isRisky());
    }

    public function testEmptySuite(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(EmptyTestCaseTest::class);
        $suite->run($this->result);

        $this->assertTrue($this->cache->status('Warning')->isWarning());
    }
}
