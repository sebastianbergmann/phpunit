<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface TestResultCache
{
    public function setStatus(string $testName, TestStatus $status): void;

    public function status(string $testName): TestStatus;

    public function setTime(string $testName, float $time): void;

    public function time(string $testName): float;

    public function load(): void;

    public function persist(): void;
}
