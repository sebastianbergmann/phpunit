<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelReturnValues;

use PHPUnit\Framework\TestCase;

interface Dependency
{
}

final readonly class ValueWithDependency
{
    public Dependency $dependency;

    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

final class DTestDoubleReturningTest extends TestCase
{
    public function testReturnsAnObjectThatHoldsATestDouble(): ValueWithDependency
    {
        $this->assertTrue(true);

        return new ValueWithDependency($this->createStub(Dependency::class));
    }
}
