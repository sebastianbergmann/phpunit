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

use Exception;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\TestCase;

final class ExceptionInFirstOfTwoBeforeClassMethodsTest extends TestCase
{
    #[BeforeClass(priority: 2)]
    public static function beforeClassOne(): void
    {
        throw new Exception;
    }

    #[BeforeClass(priority: 1)]
    public static function beforeClassTwo(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
