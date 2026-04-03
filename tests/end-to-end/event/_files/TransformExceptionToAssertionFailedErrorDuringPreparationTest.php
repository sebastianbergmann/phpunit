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

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class TransformExceptionToAssertionFailedErrorDuringPreparationTest extends TestCase
{
    protected function setUp(): void
    {
        throw new RuntimeException('setup failed');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    protected function transformException(Throwable $t): Throwable
    {
        return new AssertionFailedError($t->getMessage(), 0, $t);
    }
}
