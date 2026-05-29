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

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Framework\TestCase;

final class HookFixture extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function successfulHook(): void
    {
    }

    public function secondSuccessfulHook(): void
    {
    }

    public function throwingHook(): void
    {
        throw new Exception('hook errored');
    }

    public function failingHook(): void
    {
        throw new AssertionFailedError('hook failed');
    }

    public function skippingHook(): void
    {
        throw new SkippedWithMessageException('hook skipped');
    }
}
