<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\IgnoreMethodUsingAttribute;

use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;
use PHPUnit\Framework\TestCase;

#[IgnoreMethodForCodeCoverage(CoveredClass::class, 'n')]
final class IgnoreMethodForCodeCoverageTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue((new CoveredClass)->m());
    }
}
