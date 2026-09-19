<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\Success;

#[CoversClass(RepeatTestSuite::class)]
#[Small]
final class RepeatTestSuiteTest extends TestCase
{
    public function testCountReturnsNumberOfRepetitions(): void
    {
        $tests = [
            new Success('testOne'),
            new Success('testOne'),
            new Success('testOne'),
        ];

        $suite = RepeatTestSuite::fromTests('PHPUnit\TestFixture\Success::testOne', $this->createStub(Emitter::class), $tests, 1);

        $this->assertCount(3, $suite);
    }

    public function testNameReturnsValueProvidedToFactory(): void
    {
        $suite = RepeatTestSuite::fromTests(
            'PHPUnit\TestFixture\Success::testOne',
            $this->createStub(Emitter::class),
            [new Success('testOne')],
            1,
        );

        $this->assertSame('PHPUnit\TestFixture\Success::testOne', $suite->name());
    }

    public function testSortIdDelegatesToFirstChild(): void
    {
        $test = new Success('testOne');

        $suite = RepeatTestSuite::fromTests(
            'PHPUnit\TestFixture\Success::testOne',
            $this->createStub(Emitter::class),
            [$test],
            1,
        );

        $this->assertSame($test->sortId(), $suite->sortId());
    }
}
