<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use function usleep;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class SlowGenerationTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersThatTakeTwoSeconds(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                usleep(2_000_000);

                return $value;
            },
        );
    }

    /**
     * @return ValueGenerator<int>
     */
    public static function integersThatTakeMoreThanOneSecond(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                usleep(1_200_000);

                return $value;
            },
        );
    }

    #[Property]
    public function testArgumentsTakeTooLongToBeGenerated(#[ForAll('integersThatTakeTwoSeconds')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testArgumentsAndExampleTakeTooLongTogether(#[ForAll('integersThatTakeMoreThanOneSecond')] int $value): void
    {
        usleep(1_200_000);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
