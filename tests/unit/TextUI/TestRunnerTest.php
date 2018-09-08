<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use PHPUnit\Framework\TestCase;

class TestRunnerTest extends TestCase
{
    public function testTestIsRunnable()
    {
        $runner = new TestRunner();
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun(new \Success(), ['filter' => 'foo'], false);
    }

    public function testSuiteIsRunnable()
    {
        $runner = new TestRunner();
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun($this->getSuiteMock(), ['filter' => 'foo'], false);
    }

    /**
     * @return \PHPUnit\TextUI\ResultPrinter
     */
    private function getResultPrinterMock()
    {
        return $this->createMock(\PHPUnit\TextUI\ResultPrinter::class);
    }

    /**
     * @return \PHPUnit\Framework\TestSuite
     */
    private function getSuiteMock()
    {
        $suite = $this->createMock(\PHPUnit\Framework\TestSuite::class);
        $suite->expects($this->once())->method('injectFilter');
        $suite->expects($this->once())->method('run');

        return $suite;
    }
}
