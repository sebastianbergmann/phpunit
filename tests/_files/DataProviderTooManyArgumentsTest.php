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

final class DataProviderTooManyArgumentsTest extends TestCase
{
    public static function provider(): iterable
    {
        // correct case, 2nd parameter is not required
        yield [true];

        // correct case
        yield [true, true];

        // incorrect case
        yield [true, true, 'Third argument, but test method only has two.'];
    }

    #[DataProvider('provider')]
    public function testMethodHavingTwoParameters(bool $x1, bool $x2 = true): void
    {
        $this->assertSame($x1, $x2);
    }

    #[DataProvider('provider')]
    public function testMethodHavingVariadicParameter(bool $x1, ...$rest): void
    {
        $this->assertTrue($x1);
    }

    public function testToNotHaveNoTests(): void
    {
        $this->assertTrue(true);
    }
}
