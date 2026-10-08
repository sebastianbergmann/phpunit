<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class PropertyTest extends TestCase
{
    #[Property(examples: 500)]
    public function testWithNumberOfExamples(int $value): void
    {
    }

    #[Property]
    public function testWithoutNumberOfExamples(int $value): void
    {
    }

    #[Property(examples: 0)]
    public function testWithInvalidNumberOfExamples(int $value): void
    {
    }
}
