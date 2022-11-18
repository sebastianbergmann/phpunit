<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use PHPUnit\Framework\TestCase;

/**
 * @testdox Text from class-level TestDox metadata
 */
final class DataProviderWithStringDataSetNameAndMetadataWithPlaceholdersTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'data set name' => [
                'string',
                0,
                0.0,
                ['key' => 'value'],
                true,
                Foo::BAR,
            ],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @testdox Text from method-level TestDox metadata for successful test with placeholders ($a, $b, $c $d, $e, $f)
     */
    public function testSomethingThatWorks(string $a, int $b, float $c, array $d, bool $e, Foo $f): void
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider provider
     *
     * @testdox Text from method-level TestDox metadata for failing test with placeholders ($a, $b, $c $d, $e, $f)
     */
    public function testSomethingThatDoesNotWork(string $a, int $b, float $c, array $d, bool $e, Foo $f): void
    {
        /* @noinspection PhpUnitAssertTrueWithIncompatibleTypeArgumentInspection */
        $this->assertTrue(false);
    }
}
