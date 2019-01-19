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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class NullTestResultCache implements TestResultCacheInterface
{
    public function getState($testName): int
    {
        return BaseTestRunner::STATUS_UNKNOWN;
    }

    public function getTime($testName): float
    {
        return 0;
    }

    public function load(): void
    {
    }

    public function persist(): void
    {
    }
}
