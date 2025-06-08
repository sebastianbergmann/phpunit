<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(StaticAnalysisForCodeCoverageFinished::class)]
#[Small]
final class StaticAnalysisForCodeCoverageFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $cacheHits     = 1;
        $cacheMisses   = 2;

        $event = new StaticAnalysisForCodeCoverageFinished(
            $telemetryInfo,
            $cacheHits,
            $cacheMisses,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($cacheHits, $event->cacheHits());
        $this->assertSame($cacheMisses, $event->cacheMisses());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new StaticAnalysisForCodeCoverageFinished(
            $this->telemetryInfo(),
            1,
            2,
        );

        $this->assertSame('Static Analysis for Code Coverage Finished (1 cache hits, 2 cache misses)', $event->asString());
    }
}
