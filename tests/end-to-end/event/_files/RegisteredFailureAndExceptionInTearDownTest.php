<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RegisteredFailureAndExceptionInTearDownTest extends TestCase
{
    protected function setUp(): void
    {
        $this->registerFailureType(RuntimeException::class);
    }

    protected function tearDown(): void
    {
        throw new LogicException('message');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);

        throw new RuntimeException('message');
    }
}
