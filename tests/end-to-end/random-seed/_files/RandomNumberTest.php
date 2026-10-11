<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\RandomSeed;

use const PHP_EOL;
use function mt_rand;
use Countable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RandomNumberTest extends TestCase
{
    public static function provider(): array
    {
        return [[mt_rand()]];
    }

    public static function setUpBeforeClass(): void
    {
        print 'setUpBeforeClass(): ' . mt_rand() . PHP_EOL;
    }

    protected function setUp(): void
    {
        $this->createStub(Countable::class);
    }

    public function testOne(): void
    {
        print 'testOne(): ' . mt_rand() . PHP_EOL;

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        print 'testTwo(): ' . mt_rand() . PHP_EOL;

        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    public function testThree(int $randomNumberFromDataProvider): void
    {
        print 'provider(): ' . $randomNumberFromDataProvider . PHP_EOL;
        print 'testThree(): ' . mt_rand() . PHP_EOL;

        $this->assertTrue(true);
    }
}
