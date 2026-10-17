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
use PHPUnit\Framework\TestCase;

final class ExplicitExampleWithArgumentOfWrongTypeTest extends TestCase
{
    #[Property]
    #[TestWith(['one'])]
    public function testOne(int $value): void
    {
        $this->assertIsInt($value);
    }
}
