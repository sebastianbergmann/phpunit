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
use PHPUnit\Framework\Attributes\ForAllExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\UseGenerators;
use PHPUnit\Framework\TestCase;

// the class is loaded when the generators of a property are collected, while
// the test suite is built
spl_autoload_register(
    static function (string $className): void
    {
        if ($className === ClassWithParentClassThatDoesNotExist::class) {
            require __DIR__ . '/ClassWithParentClassThatDoesNotExist.php';
        }
    },
);

final class ClassOfGeneratorsThatCannotBeLoadedTest extends TestCase
{
    #[Property]
    public function testSpecifiesTheGeneratorForAParameterWithAMethodOfTheClass(#[ForAllExternal(ClassWithParentClassThatDoesNotExist::class, 'generator')] int $value): void
    {
    }

    #[Property]
    #[UseGenerators(ClassWithParentClassThatDoesNotExist::class)]
    public function testUsesTheGeneratorsThatTheClassRegisters(int $value): void
    {
    }

    public function testIsRun(): void
    {
        $this->assertTrue(true);
    }
}
