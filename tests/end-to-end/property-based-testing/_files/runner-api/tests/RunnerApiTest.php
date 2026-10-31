<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use function count;
use function sleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RunnerApiTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testPasses(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testFailsForValuesGreaterThan100(int $value): void
    {
        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testErrors(int $value): void
    {
        throw new RuntimeException('error for ' . $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsRejected(int $value): void
    {
        $this->assume(false);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testExceedsItsTimeLimit(int $value): void
    {
        sleep(2);
    }

    /**
     * @param list<int<0, 9>> $values
     */
    #[Property]
    public function testDrawsAList(array $values): void
    {
        $this->assertLessThanOrEqual(10, count($values));
    }

    /**
     * @param list<int<0, 9>> $values
     */
    #[Property]
    public function testDrawsAValue(array $values): void
    {
        $this->assertIsInt($this->draw(Generators::integers(0, 9), 'digit'));
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsSkipped(int $value): void
    {
        $this->markTestSkipped('skipped');
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsIncomplete(int $value): void
    {
        $this->markTestIncomplete('incomplete');
    }

    public function testIsNotAProperty(): void
    {
        $this->assertTrue(true);
    }
}
