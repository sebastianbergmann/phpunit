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

use PHPUnit\Framework\TestCase;

final class HookInvocationRecordingFixture extends TestCase
{
    public bool $hookWasInvoked = false;

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function recordingHook(): void
    {
        $this->hookWasInvoked = true;
    }
}
