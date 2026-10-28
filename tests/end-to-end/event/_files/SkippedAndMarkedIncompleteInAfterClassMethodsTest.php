<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\TestCase;

final class SkippedAndMarkedIncompleteInAfterClassMethodsTest extends TestCase
{
    #[AfterClass]
    public static function skip(): void
    {
        self::markTestSkipped('message');
    }

    #[AfterClass]
    public static function markIncomplete(): void
    {
        self::markTestIncomplete('message');
    }

    #[AfterClass]
    public static function afterLastTest(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
