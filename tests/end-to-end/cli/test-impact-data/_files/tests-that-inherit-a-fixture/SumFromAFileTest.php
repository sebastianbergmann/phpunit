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

require_once __DIR__ . '/TestCaseThatReadsSums.php';

#[CoversClass(Calculator::class)]
final class SumFromAFileTest extends TestCaseThatReadsSums
{
    public function testAdds(): void
    {
        foreach ($this->sums() as [$a, $b, $expected]) {
            $this->assertSame($expected, (new Calculator)->add($a, $b));
        }
    }
}
