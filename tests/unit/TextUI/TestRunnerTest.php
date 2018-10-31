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
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\MissingCoversAnnotationException;

class TestRunnerTest extends TestCase
{
    public function testTestIsRunnable(): void
    {
        $runner = new TestRunner();
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun(new \Success(), ['filter' => 'foo'], false);
    }

    public function testSuiteIsRunnable(): void
    {
        $runner = new TestRunner();
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun($this->getSuiteMock(), ['filter' => 'foo'], false);
    }

    public function testCheckForMissingCoversIfCoversAnnotationsAreForced(): void
    {
        if (!\extension_loaded('xdebug')) {
            $this->markTestSkipped('skip: xdebug not loaded');
        }
        $coverageFilter = new Filter();
        $coverageFilter->addFileToWhitelist('foo');
        $runner = new TestRunner(null, $coverageFilter);
        $runner->setPrinter($this->getResultPrinterMock());

        $arguments = [
            'filter'                         => 'foo',
            'coverageText'                   => true,
            'forceCoversAnnotation'          => true,
            'coverageTextShowUncoveredFiles' => false,
            'coverageTextShowOnlySummary'    => true,
        ];
        $result       = $runner->doRun($this->getSuiteMock(), $arguments, false);
        $codeCoverage = $result->getCodeCoverage();

        $this->expectException(MissingCoversAnnotationException::class);
        $codeCoverage->append([], 'foo');
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
