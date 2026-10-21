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

require_once __DIR__ . '/EmailAddress.php';

require_once __DIR__ . '/Customer.php';

require_once __DIR__ . '/GeneratorForEmailAddresses.php';

require_once __DIR__ . '/Tree.php';

final class GeneratorForTest extends TestCase
{
    use GeneratorForEmailAddresses;

    /**
     * @return ValueGenerator<Tree>
     */
    #[GeneratorFor(Tree::class)]
    public static function treesOfDigits(): ValueGenerator
    {
        return Generators::tuples(
            Generators::integers(0, 9),
            Generators::lists(Generators::objects(Tree::class), recursive: true),
        )->map(
            static function (array $tree): Tree
            {
                return new Tree($tree[0], $tree[1]);
            },
        );
    }

    #[Property]
    public function testRegisteredGeneratorIsUsedForAParameter(EmailAddress $emailAddress): void
    {
        $this->assertStringEndsWith('@example.com', $emailAddress->asString());
    }

    #[Property]
    public function testRegisteredGeneratorIsUsedForAConstructorParameter(Customer $customer, EmailAddress $newAddress): void
    {
        $this->assertStringEndsWith('@example.com', $customer->emailAddress()->asString());

        $customer->changeEmailAddress($newAddress);

        $this->assertSame($newAddress, $customer->emailAddress());
    }

    #[Property]
    public function testRegisteredGeneratorCanBeRecursive(Tree $tree): void
    {
        foreach ($tree->values() as $value) {
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThanOrEqual(9, $value);
        }
    }
}
