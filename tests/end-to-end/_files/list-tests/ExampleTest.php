<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ListTestsXml;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [1, 2, 3],
        ];
    }

    #[DataProvider('provider')]
    #[Group('example')]
    public function testOne(int $a, int $b, int $c): void
    {
        $this->assertTrue(true);
    }

    #[Group('another-example')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
