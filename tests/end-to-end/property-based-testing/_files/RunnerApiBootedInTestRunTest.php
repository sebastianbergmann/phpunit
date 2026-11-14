<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\PropertyBased\AlreadyBootedException;
use PHPUnit\Runner\PropertyBased\PropertyRunner;

final class RunnerApiBootedInTestRunTest extends TestCase
{
    public function testCannotBootPhpunit(): void
    {
        try {
            PropertyRunner::boot(__DIR__ . '/runner-api/phpunit.xml');
        } catch (AlreadyBootedException $e) {
            $this->assertSame('PHPUnit cannot be booted because it is already running in this process', $e->getMessage());

            return;
        }

        $this->fail('PHPUnit was booted');
    }

    public function testIsRunAfterPhpunitWasNotBooted(): void
    {
        $this->assertTrue(true);
    }
}
