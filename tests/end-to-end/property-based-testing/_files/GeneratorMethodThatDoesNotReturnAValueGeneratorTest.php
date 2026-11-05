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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class GeneratorMethodThatDoesNotReturnAValueGeneratorTest extends TestCase
{
    public static function integers(): mixed
    {
        return 42;
    }

    #[Property]
    public function testUsesAGeneratorMethodThatDoesNotReturnAValueGenerator(#[ForAll('integers')] int $value): void
    {
    }
}
