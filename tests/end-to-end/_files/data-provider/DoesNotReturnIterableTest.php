<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DoesNotReturnIterableTest extends TestCase
{
    public static function values(): int
    {
        return 42;
    }

    #[DataProvider('values')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
