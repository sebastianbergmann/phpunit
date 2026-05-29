<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutputBufferStopResult::class)]
#[Small]
final class OutputBufferStopResultTest extends TestCase
{
    public function testHoldsCleanCloseWithoutRiskyMessage(): void
    {
        $result = new OutputBufferStopResult(true, null);

        $this->assertTrue($result->closedCleanly);
        $this->assertNull($result->riskyMessage);
    }

    public function testHoldsUncleanCloseWithRiskyMessage(): void
    {
        $result = new OutputBufferStopResult(false, 'closed extra buffers');

        $this->assertFalse($result->closedCleanly);
        $this->assertSame('closed extra buffers', $result->riskyMessage);
    }
}
