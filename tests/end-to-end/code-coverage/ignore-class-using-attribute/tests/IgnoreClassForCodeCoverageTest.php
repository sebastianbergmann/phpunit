<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\IgnoreClassUsingAttribute;

use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use PHPUnit\Framework\TestCase;

#[IgnoreClassForCodeCoverage(CoveredClass::class)]
final class IgnoreClassForCodeCoverageTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue((new CoveredClass)->m());
    }
}
