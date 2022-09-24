<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\TestDox;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\SummaryPrinter;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private Printer $printer;
    private bool $colors;

    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer = $printer;
        $this->colors  = $colors;
    }

    /**
     * @psalm-param array<class-string,array{test: TestMethod, duration: Duration, status: TestStatus, testDoubles: list<class-string|trait-string>}> $tests
     */
    public function print(TestResult $result, array $tests): void
    {
        (new SummaryPrinter($this->printer, $this->colors))->print($result);
    }

    public function flush(): void
    {
        $this->printer->flush();
    }
}
