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

use Throwable;

interface TestResult
{
    public function isTestSuccessful(): bool;

    public function fail(
        string $symbol,
        Throwable $throwable,
        bool $verbose = false
    ): void;

    public function setRuntime(float $runtime): void;

    public function toString(?self $previousTestResult, $verbose = false): string;
}
