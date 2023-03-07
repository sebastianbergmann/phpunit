<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use PHPUnit\Framework\Attributes\IgnoreFunctionForCodeCoverage;
use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;
use PHPUnit\Framework\TestCase;

#[IgnoreClassForCodeCoverage(CoveredParentClass::class)]
#[IgnoreMethodForCodeCoverage(CoveredClass::class, 'protectedMethod')]
#[IgnoreFunctionForCodeCoverage('globalFunction')]
final class IgnoringCodeUnitsTest extends TestCase
{
    public function testOne(): void
    {
    }
}
