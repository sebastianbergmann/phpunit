<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\NestedTestRun;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;

final class TestThatIsRunByAnotherTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}

final class TestThatRunsAnotherTest extends TestCase
{
    public function testRunsAnotherTest(): void
    {
        new TestThatIsRunByAnotherTest('testOne')->run();
    }

    #[RunInSeparateProcess]
    public function testRunsAnotherTestInSeparateProcess(): void
    {
        new TestThatIsRunByAnotherTest('testOne')->run();
    }

    public function testRunsPhptTest(): void
    {
        new PhptTestCase(__DIR__ . '/../../../_files/success.phpt')->run();
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
