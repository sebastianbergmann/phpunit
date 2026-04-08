<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue4975Test extends TestCase
{
    public static function provider(): array
    {
        return [
            '#1 first'  => [true],
            '#2 second' => [true],
            '#3 third'  => [true],
        ];
    }

    #[DataProvider('provider')]
    public function testSomething($value): void
    {
        $this->assertTrue($value);
    }
}
