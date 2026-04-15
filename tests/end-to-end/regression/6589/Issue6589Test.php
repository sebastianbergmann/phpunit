<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6589;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue6589Test extends TestCase
{
    public static function provider(): iterable
    {
        yield [9.223372036854776e18];
    }

    #[DataProvider('provider')]
    public function testOne(float $value): void
    {
        $this->assertIsFloat($value);
    }
}
