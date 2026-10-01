<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSameClassDepends;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\TestCase;

final class SameClassDependsTest extends TestCase
{
    public static function provider(): array
    {
        return [[1], [2]];
    }

    public static function emptyProvider(): array
    {
        return [];
    }

    public function testProducer(): string
    {
        $this->assertTrue(true);

        return 'value';
    }

    #[Depends('testProducer')]
    public function testConsumer(string $value): void
    {
        $this->assertSame('value', $value);
    }

    public function testFailingProducer(): void
    {
        $this->fail('failure');
    }

    #[Depends('testFailingProducer')]
    public function testConsumerOfFailingProducer(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    public function testDataProvidedProducer(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    #[Depends('testDataProvidedProducer')]
    public function testConsumerOfDataProvidedProducer(): void
    {
        $this->assertTrue(true);
    }

    #[Repeat(2)]
    public function testRepeatedProducer(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testRepeatedProducer')]
    public function testConsumerOfRepeatedProducer(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testThatDoesNotExist')]
    public function testConsumerOfTestThatDoesNotExist(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('emptyProvider', skipWhenEmpty: true)]
    public function testWithDataProviderThatProvidesNoData(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
