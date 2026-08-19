<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunHistory\SkippedSuite;

use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\TestCase;

final class SkippedSuiteTest extends TestCase
{
    #[BeforeClass]
    public static function skipTheTestSuite(): void
    {
        self::markTestSkipped('the whole test suite is skipped');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
