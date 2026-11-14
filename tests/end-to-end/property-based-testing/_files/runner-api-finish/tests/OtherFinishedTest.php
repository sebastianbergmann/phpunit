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

use const PHP_EOL;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class OtherFinishedTest extends TestCase
{
    private static int $preparations = 0;

    public static function setUpBeforeClass(): void
    {
        self::$preparations++;

        print 'OtherFinishedTest::setUpBeforeClass() was called, for preparation ' . self::$preparations . PHP_EOL;
    }

    public static function tearDownAfterClass(): void
    {
        print 'OtherFinishedTest::tearDownAfterClass() was called, for preparation ' . self::$preparations . PHP_EOL;
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testPasses(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
