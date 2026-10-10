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

use function spl_autoload_register;
use PHPUnit\Framework\Attributes\GeneratorFor;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

// the class is loaded when the generators that are registered with
// #[GeneratorFor] are collected, while the test suite is built
spl_autoload_register(
    static function (string $className): void
    {
        if ($className === ClassWithParentClassThatDoesNotExist::class) {
            require __DIR__ . '/ClassWithParentClassThatDoesNotExist.php';
        }
    },
);

final class GeneratorForClassThatCannotBeLoadedTest extends TestCase
{
    /**
     * @return ValueGenerator<null>
     */
    #[GeneratorFor(ClassWithParentClassThatDoesNotExist::class)]
    public static function objectsOfClassThatCannotBeLoaded(): ValueGenerator
    {
        return Generators::constant(null);
    }

    #[Property]
    public function testIsChecked(int $value): void
    {
    }

    public function testIsRun(): void
    {
        $this->assertTrue(true);
    }
}
