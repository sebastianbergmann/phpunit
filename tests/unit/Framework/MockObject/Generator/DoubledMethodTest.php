<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Generator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

#[CoversClass(DoubledMethod::class)]
#[Group('test-doubles')]
#[Small]
final class DoubledMethodTest extends TestCase
{
    /**
     * ReflectionProperty::setValue() is an internal method with an optional
     * parameter that does not have a default value. Such a parameter has to
     * be declared with null as its default value in the doubled method.
     */
    public function testDeclaresOptionalParameterThatDoesNotHaveDefaultValueWithNullAsDefaultValue(): void
    {
        $method = DoubledMethod::fromReflection(
            new ReflectionMethod(ReflectionProperty::class, 'setValue'),
        );

        $this->assertStringContainsString('$value = null', $method->generateCode());
    }
}
