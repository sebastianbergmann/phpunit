<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * A test class with data providers, so that tests of the parallel runner can
 * describe data-provided test cases in the parent process and rebuild them the
 * way a worker process does: by invoking the data provider again.
 */
final class WorkerDataProvidedTest extends TestCase
{
    public static int $namedProviderInvocations = 0;

    /**
     * @return array<string, array{int}>
     */
    public static function namedProvider(): array
    {
        self::$namedProviderInvocations++;

        return [
            'first data set'  => [1],
            'second data set' => [2],
        ];
    }

    /**
     * @return list<array{int}>
     */
    public static function numberedProvider(): array
    {
        return [
            [1],
            [2],
        ];
    }

    /**
     * @return list<array{stdClass}>
     */
    public static function objectProvider(): array
    {
        $object = new stdClass;

        $object->value = 'provided by the data provider';

        return [
            [$object],
        ];
    }

    public static function failingProvider(): never
    {
        throw new RuntimeException('the data provider failed');
    }

    #[DataProvider('namedProvider')]
    public function testWithNamedDataSets(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    #[DataProvider('numberedProvider')]
    public function testWithNumberedDataSets(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    #[DataProvider('objectProvider')]
    public function testWithAnObject(stdClass $object): void
    {
        $this->assertSame('provided by the data provider', $object->value);
    }

    #[DataProvider('failingProvider')]
    public function testWithAFailingDataProvider(mixed $value): void
    {
        $this->assertNotNull($value);
    }
}
