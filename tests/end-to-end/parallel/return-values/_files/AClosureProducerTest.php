<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelReturnValues;

use Closure;
use PHPUnit\Framework\TestCase;

final class AClosureProducerTest extends TestCase
{
    public function testReturnsAClosure(): Closure
    {
        $this->assertTrue(true);

        return static fn (): int => 42;
    }
}
