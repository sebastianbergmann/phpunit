<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\TestCase;

final class ValueObjectTriggeringDeprecationWhenCloned
{
    public function __clone(): void
    {
        $this->dynamicProperty = 1;
    }
}

final class DeepCloneDependencyTriggeringDeprecationTest extends TestCase
{
    public function testProducer(): ValueObjectTriggeringDeprecationWhenCloned
    {
        $this->assertTrue(true);

        return new ValueObjectTriggeringDeprecationWhenCloned;
    }

    #[DependsUsingDeepClone('testProducer')]
    public function testConsumer(ValueObjectTriggeringDeprecationWhenCloned $value): void
    {
        $this->assertTrue(true);
    }
}
