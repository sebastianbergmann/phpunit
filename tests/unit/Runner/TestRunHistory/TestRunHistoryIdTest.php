<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(TestRunHistoryId::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-run-history')]
final class TestRunHistoryIdTest extends TestCase
{
    /**
     * @return iterable<array{string, TestRunHistoryId}>
     */
    public static function provideTestRunHistoryIds(): iterable
    {
        yield ['PHPUnit\Runner\TestRunHistory\TestRunHistoryIdTest::a method', TestRunHistoryId::fromTestClassAndMethodName(self::class, 'a method')];

        yield ['PHPUnit\Runner\TestRunHistory\TestRunHistoryIdTest::testMethod', TestRunHistoryId::fromTest(self::testMethod())];
    }

    #[DataProvider('provideTestRunHistoryIds')]
    public function testTestRunHistoryId(string $expectedString, TestRunHistoryId $testRunHistoryId): void
    {
        $this->assertSame($expectedString, $testRunHistoryId->asString());
    }

    public function testReorderableTestRunHistoryId(): void
    {
        $reorderable = $this;
        $this->assertInstanceOf(Reorderable::class, $reorderable);

        $this->assertSame('PHPUnit\Runner\TestRunHistory\TestRunHistoryIdTest::testReorderableTestRunHistoryId', TestRunHistoryId::fromReorderable($reorderable)->asString());
    }

    public function testFromTestClassAndMethodName(): void
    {
        $this->assertSame(
            'PHPUnit\Runner\TestRunHistory\TestRunHistoryIdTest::someMethod',
            TestRunHistoryId::fromTestClassAndMethodName(self::class, 'someMethod')->asString(),
        );
    }

    public function testPhptTestRunHistoryId(): void
    {
        $file     = 'test.phpt';
        $phptTest = new Phpt($file);

        $this->assertSame('test.phpt', TestRunHistoryId::fromTest($phptTest)->asString());
    }

    private static function testMethod(): TestMethod
    {
        return new TestMethod(
            self::class,
            'testMethod',
            'TestClass.php',
            1,
            new TestDox('', '', ''),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
