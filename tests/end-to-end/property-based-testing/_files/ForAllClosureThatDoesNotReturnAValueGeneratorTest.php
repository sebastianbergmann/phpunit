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

final class ForAllClosureThatDoesNotReturnAValueGeneratorTest extends TestCase
{
    #[Property]
    public function testUsesAClosureThatDoesNotReturnAValueGenerator(
        #[ForAll(static function (): mixed
        {
            return 42;
        })]
        int $value,
    ): void {
    }
}
