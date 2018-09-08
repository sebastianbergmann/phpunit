<?php
declare(strict_types=1);
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

class OriginalTypeGeneratedTest extends TestCase
{
    public function testPrologueContainsNoUndefinedNamespace(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Foo')
        );

        $this->assertEquals(
            "class Foo\n{\n}\n\n",
            $originalType->getCodePrologue()
        );
    }

    public function testEpilogueIsEmptyForUndefinedNamespace(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Foo')
        );

        $this->assertEquals(
            '',
            $originalType->getCodeEpilogue()
        );
    }

    public function testPrologueContainsDefinedNamespace(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );

        $this->assertEquals(
            "namespace Bar {\n\nclass Foo\n{\n}\n\n}\n\nnamespace {\n\n",
            $originalType->getCodePrologue()
        );
    }

    public function testEpilogueClosesDefinedNamespace(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );

        $this->assertEquals(
            "\n\n}",
            $originalType->getCodeEpilogue()
        );
    }

    public function testHasNoToStringMethod(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->assertFalse($originalType->hasMethod('__toString'));
    }

    public function testFailsToGetMethod(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->expectException(\OutOfBoundsException::class);
        $originalType->getMethod('__toString');
    }

    public function testHasNoMethods(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->assertEquals([], $originalType->getMethods());
    }

    public function testIsNoInterface(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->assertFalse($originalType->isInterface());
    }

    public function testIsNotFinal(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->assertFalse($originalType->isFinal());
    }

    public function testPreservesName(): void
    {
        $typeName     = TypeName::fromQualifiedName('Bar\\Foo');
        $originalType = new OriginalTypeGenerated($typeName);
        $this->assertSame($typeName, $originalType->getName());
    }

    public function testDoesNotImplementInterface(): void
    {
        $originalType = new OriginalTypeGenerated(
            TypeName::fromQualifiedName('Bar\\Foo')
        );
        $this->assertFalse($originalType->implementsInterface(\Traversable::class));
    }
}
