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

use function is_int;
use function spl_autoload_register;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

// the class is loaded when the generator for the parameter of the property is
// derived, while the test suite is built
spl_autoload_register(
    static function (string $className): void
    {
        if ($className === ClassThatTriggersADeprecationWhenItIsLoaded::class) {
            require __DIR__ . '/ClassThatTriggersADeprecationWhenItIsLoaded.php';
        }
    },
);

final class IssueTriggeredWhileGeneratorIsDerivedTest extends TestCase
{
    #[Property(examples: 3)]
    public function testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded(ClassThatTriggersADeprecationWhenItIsLoaded $object): void
    {
        $this->assertTrue(is_int($object->value()));
    }
}
