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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class SkippedInSetUpBeforeClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped('the test class is skipped');
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testIsNeverRun(int $value): void
    {
        $this->assertTrue(true);
    }
}
