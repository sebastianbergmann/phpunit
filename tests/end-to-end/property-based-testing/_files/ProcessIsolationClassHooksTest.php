<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ProcessIsolationClassHooksTest extends TestCase
{
    private static ?string $state = null;

    public static function setUpBeforeClass(): void
    {
        self::$state = 'prepared';
    }

    public static function tearDownAfterClass(): void
    {
        self::$state = null;
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 3)]
    public function testSeesWhatTheHookMethodBeforeTheFirstTestSetUp(int $value): void
    {
        $this->assertSame('prepared', self::$state);
    }
}
