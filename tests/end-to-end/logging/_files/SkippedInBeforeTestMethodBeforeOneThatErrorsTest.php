<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use Exception;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class SkippedInBeforeTestMethodBeforeOneThatErrorsTest extends TestCase
{
    #[Before(priority: 2)]
    public function skipUnlessServiceIsAvailable(): void
    {
        if ($this->name() === 'testOne') {
            $this->markTestSkipped('service is not available');
        }
    }

    #[Before(priority: 1)]
    public function connectToService(): void
    {
        if ($this->name() === 'testOne') {
            throw new Exception('could not connect to service');
        }
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
