<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function iterator_to_array;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(TestResultCollection::class)]
#[CoversClass(TestResultCollectionIterator::class)]
#[Group('testdox')]
#[Small]
final class TestResultCollectionTest extends TestCase
{
    public function test_An_empty_collection_can_be_created(): void
    {
        $collection = TestResultCollection::fromArray([]);

        $this->assertSame([], $collection->asArray());
        $this->assertSame([], iterator_to_array($collection));
    }

    public function test_Returns_test_results_in_the_order_they_were_provided(): void
    {
        $first  = $this->testResult('alpha');
        $second = $this->testResult('beta');

        $collection = TestResultCollection::fromArray([$first, $second]);

        $this->assertSame([$first, $second], $collection->asArray());
        $this->assertSame([$first, $second], iterator_to_array($collection));
    }

    public function test_Iterator_yields_zero_based_keys(): void
    {
        $first  = $this->testResult('alpha');
        $second = $this->testResult('beta');
        $third  = $this->testResult('gamma');

        $iterator = (TestResultCollection::fromArray([$first, $second, $third]))->getIterator();

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertSame($first, $iterator->current());

        $iterator->next();
        $this->assertSame(1, $iterator->key());
        $this->assertSame($second, $iterator->current());

        $iterator->next();
        $this->assertSame(2, $iterator->key());
        $this->assertSame($third, $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function test_Iterator_can_be_rewound_after_traversal(): void
    {
        $first  = $this->testResult('alpha');
        $second = $this->testResult('beta');

        $iterator = (TestResultCollection::fromArray([$first, $second]))->getIterator();

        $iterator->rewind();
        $iterator->next();
        $iterator->next();

        $this->assertFalse($iterator->valid());

        $iterator->rewind();

        $this->assertTrue($iterator->valid());
        $this->assertSame(0, $iterator->key());
        $this->assertSame($first, $iterator->current());
    }

    private function testResult(string $methodName): TestResult
    {
        return new TestResult(
            new TestMethod(
                'FooTest',
                $methodName,
                'FooTest.php',
                1,
                TestDoxBuilder::fromClassNameAndMethodName('Foo', $methodName),
                MetadataCollection::fromArray([]),
                TestDataCollection::fromArray([]),
            ),
            TestStatus::success(),
            null,
        );
    }
}
