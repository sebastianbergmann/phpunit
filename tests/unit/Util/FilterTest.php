<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Test::class)]
#[Small]
final class FilterTest extends TestCase
{
    public function testUnwrapThrowableUsesPreviousValues(): void
    {
        $first  = new Exception('first', 123, null);
        $second = new Exception('second', 345, $first);

        $this->assertSame(Filter::stackTraceFromThrowableAsString($second), Filter::stackTraceFromThrowableAsString($first));
    }
}
