<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\IgnoreFunctionUsingAttribute;

use PHPUnit\Framework\Attributes\IgnoreFunctionForCodeCoverage;
use PHPUnit\Framework\TestCase;

#[IgnoreFunctionForCodeCoverage('PHPUnit\TestFixture\IgnoreFunctionUsingAttribute\g')]
final class IgnoreFunctionForCodeCoverageTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(f());
    }
}
