<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
final class IsolatedCalculatorTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testAddsInAnotherProcess(): void
    {
        $this->assertSame(5, (new Calculator)->add(2, 3));
    }
}
