<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

class TestImplementorTest extends TestCase
{
    public function testSuccessfulRun()
    {
        $result = new TestResult;

        $test = new \DoubleTestCase(new \Success);
        $test->run($result);

        $this->assertCount(\count($test), $result);
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
    }
}
