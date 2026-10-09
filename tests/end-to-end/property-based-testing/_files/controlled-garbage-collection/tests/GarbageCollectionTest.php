<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection;

use function is_int;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class GarbageCollectionTest extends TestCase
{
    #[Property(examples: 4)]
    public function testProperty(int $value): void
    {
        $this->assertTrue(is_int($value));
    }
}
