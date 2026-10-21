<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\GeneratorFor;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Money.php';

final class GeneratorForThatRefersToItselfTest extends TestCase
{
    /**
     * @return ValueGenerator<Money>
     */
    #[GeneratorFor(Money::class)]
    public static function positiveMoney(): ValueGenerator
    {
        return Generators::objects(Money::class)->filter(
            static function (Money $money): bool
            {
                return $money->amount > 0;
            },
        );
    }

    #[Property]
    public function testGeneratorRegisteredForTheClassOfAParameterRefersToItself(Money $money): void
    {
        $this->assertGreaterThan(0, $money->amount);
    }
}
