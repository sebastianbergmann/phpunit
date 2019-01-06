<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Color;

/**
 * @group testdox
 */
final class CliTestDoxPrinterColorTest extends TestCase
{
    /**
     * @var TestableCliTestDoxPrinter
     */
    private $printer;

    protected function setUp(): void
    {
        $this->printer        = new TestableCliTestDoxPrinter(null, true, \PHPUnit\TextUI\ResultPrinter::COLOR_ALWAYS);
    }

    protected function tearDown(): void
    {
        $this->printer        = null;
    }

    public function testColorizesDiffInFailureMessage(): void
    {
        $raw     = \implode(\PHP_EOL, ['some message', '--- Expected', '+++ Actual', '@@ @@']);
        $failure = new AssertionFailedError($raw);

        $this->printer->startTest($this);
        $this->printer->addFailure($this, $failure, 0);
        $this->printer->endTest($this, 0.001);

        $this->assertStringContainsString(Color::colorize('bg-red,fg-white', 'some message'), $this->printer->getBuffer());
        $this->assertStringContainsString(Color::colorize('fg-red', '--- Expected'), $this->printer->getBuffer());
        $this->assertStringContainsString(Color::colorize('fg-green', '+++ Actual'), $this->printer->getBuffer());
        $this->assertStringContainsString(Color::colorize('fg-cyan', '@@ @@'), $this->printer->getBuffer());
    }
}
