<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

/**
 * @group testdox
 * @small
 */
final class CliTestDoxPrinterTest extends TestCase
{
    /**
     * @var TestableCliTestDoxPrinter
     */
    private $printer;

    /**
     * @var TestableCliTestDoxPrinter
     */
    private $verbosePrinter;

    protected function setUp(): void
    {
        $this->printer        = new TestableCliTestDoxPrinter;
        $this->verbosePrinter = new TestableCliTestDoxPrinter(null, true);
    }

    protected function tearDown(): void
    {
        $this->printer        = null;
        $this->verbosePrinter = null;
    }

    public function testPrintsTheClassNameOfTheTestClass(): void
    {
        $this->printer->startTest($this);
        $this->printer->endTest($this, 0);

        $this->assertStringStartsWith('PHPUnit\Util\TestDox\CliTestDoxPrinter', $this->printer->getBuffer());
    }

    public function testPrintsThePrettifiedMethodName(): void
    {
        $this->printer->startTest($this);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('Prints the prettified method name', $this->printer->getBuffer());
    }

    public function testPrintsCheckMarkForSuccessfulTest(): void
    {
        $this->printer->startTest($this);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('✔', $this->printer->getBuffer());
    }

    public function testDoesNotPrintAdditionalInformationForSuccessfulTest(): void
    {
        $this->printer->startTest($this);
        $this->printer->endTest($this, 0.001);

        $this->assertStringNotContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsCrossForTestWithError(): void
    {
        $this->printer->startTest($this);
        $this->printer->addError($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('✘', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForTestWithError(): void
    {
        $this->printer->startTest($this);
        $this->printer->addError($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsWarningTriangleForTestWithWarning(): void
    {
        $this->printer->startTest($this);
        $this->printer->addWarning($this, new Warning, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('⚠', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForTestWithWarning(): void
    {
        $this->printer->startTest($this);
        $this->printer->addWarning($this, new Warning, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsCrossForTestWithFailure(): void
    {
        $this->printer->startTest($this);
        $this->printer->addFailure($this, new AssertionFailedError, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('✘', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForTestWithFailure(): void
    {
        $this->printer->startTest($this);
        $this->printer->addFailure($this, new AssertionFailedError, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsEmptySetSymbolForTestWithFailure(): void
    {
        $this->printer->startTest($this);
        $this->printer->addIncompleteTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('∅', $this->printer->getBuffer());
    }

    public function testDoesNotPrintAdditionalInformationForIncompleteTestByDefault(): void
    {
        $this->printer->startTest($this);
        $this->printer->addIncompleteTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringNotContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForIncompleteTestInVerboseMode(): void
    {
        $this->verbosePrinter->startTest($this);
        $this->verbosePrinter->addIncompleteTest($this, new Exception('E_X_C_E_P_T_I_O_N'), 0);
        $this->verbosePrinter->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->verbosePrinter->getBuffer());
        $this->assertStringContainsString('E_X_C_E_P_T_I_O_N', $this->verbosePrinter->getBuffer());
    }

    public function testPrintsRadioactiveSymbolForRiskyTest(): void
    {
        $this->printer->startTest($this);
        $this->printer->addRiskyTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('☢', $this->printer->getBuffer());
    }

    public function testDoesNotPrintAdditionalInformationForRiskyTestByDefault(): void
    {
        $this->printer->startTest($this);
        $this->printer->addRiskyTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringNotContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForRiskyTestInVerboseMode(): void
    {
        $this->verbosePrinter->startTest($this);
        $this->verbosePrinter->addRiskyTest($this, new Exception, 0);
        $this->verbosePrinter->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->verbosePrinter->getBuffer());
    }

    public function testPrintsArrowForSkippedTest(): void
    {
        $this->printer->startTest($this);
        $this->printer->addSkippedTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString('↩', $this->printer->getBuffer());
    }

    public function testDoesNotPrintAdditionalInformationForSkippedTestByDefault(): void
    {
        $this->printer->startTest($this);
        $this->printer->addSkippedTest($this, new Exception, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringNotContainsString('│', $this->printer->getBuffer());
    }

    public function testPrintsAdditionalInformationForSkippedTestInVerboseMode(): void
    {
        $this->verbosePrinter->startTest($this);
        $this->verbosePrinter->addSkippedTest($this, new Exception('S_K_I_P_P_E_D'), 0);
        $this->verbosePrinter->endTest($this, 0.001);

        $this->assertStringContainsString('│', $this->verbosePrinter->getBuffer());
        $this->assertStringContainsString('S_K_I_P_P_E_D', $this->verbosePrinter->getBuffer());
    }
}
