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

final class ClassLevelHooksTest extends TestCase
{
    private static bool $prepared = false;

    public static function setUpBeforeClass(): void
    {
        self::$prepared = true;

        print 'setUpBeforeClass() was called' . PHP_EOL;
    }

    public static function tearDownAfterClass(): void
    {
        print 'tearDownAfterClass() was called' . PHP_EOL;
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testIsPrepared(int $value): void
    {
        $this->assertTrue(self::$prepared);
    }
}
