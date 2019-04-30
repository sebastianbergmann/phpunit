<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

final class TypeNameTest extends TestCase
{
    public function testFromReflection(): void
    {
        $class    = new \ReflectionClass(TypeName::class);
        $typeName = TypeName::fromReflection($class);

        $this->assertTrue($typeName->isNamespaced());
        $this->assertEquals('PHPUnit\\Framework\\MockObject', $typeName->getNamespaceName());
        $this->assertEquals(TypeName::class, $typeName->getQualifiedName());
        $this->assertEquals('TypeName', $typeName->getSimpleName());
    }

    public function testFromQualifiedName(): void
    {
        $typeName = TypeName::fromQualifiedName('PHPUnit\\Framework\\MockObject\\TypeName');

        $this->assertTrue($typeName->isNamespaced());
        $this->assertEquals('PHPUnit\\Framework\\MockObject', $typeName->getNamespaceName());
        $this->assertEquals('PHPUnit\\Framework\\MockObject\\TypeName', $typeName->getQualifiedName());
        $this->assertEquals('TypeName', $typeName->getSimpleName());
    }

    public function testFromQualifiedNameWithLeadingSeparator(): void
    {
        $typeName = TypeName::fromQualifiedName('\\Foo\\Bar');

        $this->assertTrue($typeName->isNamespaced());
        $this->assertEquals('Foo', $typeName->getNamespaceName());
        $this->assertEquals('Foo\\Bar', $typeName->getQualifiedName());
        $this->assertEquals('Bar', $typeName->getSimpleName());
    }

    public function testFromQualifiedNameWithoutNamespace(): void
    {
        $typeName = TypeName::fromQualifiedName('Bar');

        $this->assertFalse($typeName->isNamespaced());
        $this->assertNull($typeName->getNamespaceName());
        $this->assertEquals('Bar', $typeName->getQualifiedName());
        $this->assertEquals('Bar', $typeName->getSimpleName());
    }
}
