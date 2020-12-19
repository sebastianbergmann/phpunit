<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\TestSuite\Result
 */
final class ResultTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $count          = 9001;
        $errors         = new FailureCollection();
        $failures       = new FailureCollection();
        $notImplemented = new FailureCollection();
        $risky          = new FailureCollection();
        $skipped        = new FailureCollection();
        $warnings       = new FailureCollection();
        $passed         = [
            'foo',
            'bar',
        ];
        $passedClasses = [
            self::class,
        ];

        $result = new Result(
            $count,
            $errors,
            $failures,
            $notImplemented,
            $risky,
            $skipped,
            $warnings,
            $passed,
            $passedClasses
        );

        $this->assertSame($count, $result->count());
        $this->assertSame($errors, $result->errors());
        $this->assertSame($failures, $result->failures());
        $this->assertSame($notImplemented, $result->notImplemented());
        $this->assertSame($risky, $result->risky());
        $this->assertSame($skipped, $result->skipped());
        $this->assertSame($warnings, $result->warnings());
        $this->assertSame($passed, $result->passed());
        $this->assertSame($passedClasses, $result->passedClasses());
    }
}
