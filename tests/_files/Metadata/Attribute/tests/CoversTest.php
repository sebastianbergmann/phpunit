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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatExtendClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversNamespace('PHPUnit\TestFixture\Metadata\Attribute')]
#[CoversClass(Example::class)]
#[CoversClassesThatExtendClass(Example::class)]
#[CoversClassesThatImplementInterface(Example::class)]
#[CoversTrait(ExampleTrait::class)]
#[CoversMethod(Example::class, 'method')]
#[CoversFunction('f')]
final class CoversTest extends TestCase
{
    public function testOne(): void
    {
    }
}
