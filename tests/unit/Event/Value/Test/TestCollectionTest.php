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

use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(TestCollection::class)]
#[CoversClass(TestCollectionIterator::class)]
#[Small]
final class TestCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $test  = $this->test();
        $tests = TestCollection::fromArray([$test]);

        $this->assertSame([$test], $tests->asArray());
    }

    public function testIsCountable(): void
    {
        $test  = $this->test();
        $tests = TestCollection::fromArray([$test]);

        $this->assertCount(1, $tests);
    }

    public function testIsIterable(): void
    {
        $test  = $this->test();
        $tests = TestCollection::fromArray([$test]);

        foreach ($tests as $index => $_test) {
            $this->assertSame(0, $index);
            $this->assertSame($test, $_test);
        }
    }

    private function test(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
