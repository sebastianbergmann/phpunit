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

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Issue498Test extends TestCase
{
    public static function shouldBeTrueDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public static function shouldBeFalseDataProvider(): array
    {
        throw new Exception("Can't create the data");
    }

    #[Test]
    #[DataProvider('shouldBeTrueDataProvider')]
    #[Group('falseOnly')]
    public function shouldBeTrue($testData): void
    {
        $this->assertTrue(true);
    }

    #[Test]
    #[DataProvider('shouldBeFalseDataProvider')]
    #[Group('trueOnly')]
    public function shouldBeFalse($testData): void
    {
        $this->assertFalse(false);
    }
}
