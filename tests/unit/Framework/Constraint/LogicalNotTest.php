<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

class LogicalNotTest extends TestCase
{
    public function testNonRestrictedConstructParameterIsTreatedAsIsEqual(): void
    {
        $constraint = new LogicalNot('test');

        $this->assertSame('is not equal to \'test\'', $constraint->toString());
    }
}
