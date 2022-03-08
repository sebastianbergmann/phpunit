<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter;

final class NullPrinter implements ResultPrinter
{
    use TestListenerDefaultImplementation;

    public function printResult(TestResult $result): void
    {
    }

    public function write(string $buffer): void
    {
    }
}
