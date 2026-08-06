<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Phpt::class)]
#[CoversClass(Test::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class PhptTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $file = 'test.phpt';

        $test = new Phpt($file);

        $this->assertSame($file, $test->file());
        $this->assertSame($file, $test->id());
        $this->assertSame($file, $test->name());
        $this->assertSame($file, $test->sortId());
        $this->assertTrue($test->isPhpt());
        $this->assertFalse($test->isTestMethod());
        $this->assertSame(1, $test->repetition());
        $this->assertSame(1, $test->totalRepetitions());
        $this->assertFalse($test->isRepeated());
        $this->assertSame(1, $test->attempt());
        $this->assertSame(1, $test->maxAttempts());
        $this->assertFalse($test->isRetried());
    }

    public function testRepetitionValues(): void
    {
        $test = new Phpt('test.phpt', 2, 3);

        $this->assertSame(2, $test->repetition());
        $this->assertSame(3, $test->totalRepetitions());
        $this->assertTrue($test->isRepeated());
    }

    public function testNameIncludesRepetitionWhenRepeated(): void
    {
        $test = new Phpt('test.phpt', 2, 3);

        $this->assertSame('test.phpt (repetition 2 of 3)', $test->name());
        $this->assertSame('test.phpt (repetition 2 of 3)', $test->id());
    }

    public function testAttemptValues(): void
    {
        $test = new Phpt('test.phpt', 1, 1, 2, 3);

        $this->assertSame(2, $test->attempt());
        $this->assertSame(3, $test->maxAttempts());
        $this->assertTrue($test->isRetried());
    }

    public function testNameIncludesAttemptForAdditionalAttempts(): void
    {
        $test = new Phpt('test.phpt', 1, 1, 2, 3);

        $this->assertSame('test.phpt (attempt 2 of 3)', $test->name());
        $this->assertSame('test.phpt (attempt 2 of 3)', $test->id());
    }

    public function testNameDoesNotIncludeAttemptForFirstAttempt(): void
    {
        $test = new Phpt('test.phpt', 1, 1, 1, 3);

        $this->assertTrue($test->isRetried());
        $this->assertSame('test.phpt', $test->name());
        $this->assertSame('test.phpt', $test->id());
    }

    public function testNameIncludesRepetitionAndAttempt(): void
    {
        $test = new Phpt('test.phpt', 2, 3, 2, 3);

        $this->assertSame('test.phpt (repetition 2 of 3) (attempt 2 of 3)', $test->name());
        $this->assertSame('test.phpt (repetition 2 of 3) (attempt 2 of 3)', $test->id());
    }

    public function testSortIdIsStableAcrossRepetitionsAndAttempts(): void
    {
        $test = new Phpt('test.phpt', 2, 3, 2, 3);

        $this->assertSame('test.phpt', $test->sortId());
    }
}
