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

use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\ResultPrinter as AbstractResultPrinter;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter extends AbstractResultPrinter
{
    public function __construct(Printer $printer)
    {
        parent::__construct($printer);
    }

    public function printResult(TestResult $result): void
    {
        $this->printer()->print('TestDox result printing has not been migrated to events yet' . PHP_EOL);

        exit(1);
    }
}
