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
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\TestCase;

final class ExceptionInFirstOfTwoAfterClassMethodsTest extends TestCase
{
    #[AfterClass(priority: 2)]
    public static function afterClassOne(): void
    {
        throw new Exception;
    }

    #[AfterClass(priority: 1)]
    public static function afterClassTwo(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
