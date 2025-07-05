<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(ResultCacheId::class)]
#[Small]
final class ResultCacheIdTest extends TestCase
{
    public static function provideResultCacheIds(): iterable
    {
        yield ['PHPUnit\Runner\ResultCache\ResultCacheIdTest::a method', ResultCacheId::fromTestClassAndMethodName(self::class, 'a method')];

        yield ['PHPUnit\Runner\ResultCache\ResultCacheIdTest::testMethod', ResultCacheId::fromTest(self::testMethod())];
    }

    #[DataProvider('provideResultCacheIds')]
    public function testResultCacheId(string $expectedString, ResultCacheId $cacheId): void
    {
        $this->assertSame($expectedString, $cacheId->asString());
    }

    public function testReorderableResultCacheId(): void
    {
        $reorderable = $this;
        $this->assertInstanceOf(Reorderable::class, $reorderable);

        $this->assertSame('PHPUnit\Runner\ResultCache\ResultCacheIdTest::testReorderableResultCacheId', ResultCacheId::fromReorderable($reorderable)->asString());
    }

    public function testPhptResultCacheId(): void
    {
        $file     = 'test.phpt';
        $phptTest = new Phpt($file);

        $this->assertSame('test.phpt', ResultCacheId::fromTest($phptTest)->asString());
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
