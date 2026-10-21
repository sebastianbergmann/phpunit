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

final class GeneratorForUnknownClassTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    #[GeneratorFor('PHPUnit\TestFixture\PropertyBasedTesting\DoesNotExist')]
    public static function unknown(): ValueGenerator
    {
        return Generators::integers();
    }

    #[Property]
    public function testOne(int $value): void
    {
    }
}
