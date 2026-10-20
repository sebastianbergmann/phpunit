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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class DrawInExplicitExampleTest extends TestCase
{
    #[Property]
    #[TestWith([1])]
    public function testDrawsAValue(int $value): void
    {
        $this->assertIsInt($this->draw(Generators::integers()) + $value);
    }
}
