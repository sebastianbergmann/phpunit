<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Selection::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class SelectionTest extends TestCase
{
    public function testKnowsThatEveryTestIsRun(): void
    {
        $selection = Selection::everything('a reason');

        $this->assertTrue($selection->isEverything());
        $this->assertSame('a reason', $selection->reason());
        $this->assertSame([], $selection->tests());
        $this->assertSame(0, $selection->numberOfTestsThatAreNotRun());
    }

    public function testKnowsWhichTestsAreRun(): void
    {
        $selection = Selection::of(['FooTest::testOne'], 'a reason', 3);

        $this->assertFalse($selection->isEverything());
        $this->assertSame(['FooTest::testOne'], $selection->tests());
        $this->assertSame('a reason', $selection->reason());
    }

    public function testKnowsHowManyTestsAreNotRun(): void
    {
        $this->assertSame(2, Selection::of(['FooTest::testOne'], 'a reason', 3)->numberOfTestsThatAreNotRun());
    }
}
