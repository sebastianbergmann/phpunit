<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use ArrayObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresSetting;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\DocComments\ClassThatUsesTraitWithDocComment;
use PHPUnit\TestFixture\PropertyBased\DocComments\ClassWithDocComments;
use ReflectionMethod;

#[CoversClass(DocComments::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class DocCommentsTest extends TestCase
{
    public function testReturnsTheDocCommentOfAMethod(): void
    {
        $method = new ReflectionMethod(ClassWithDocComments::class, 'methodWithDocComment');

        $this->assertSame("/**\n     * @param int<0, 10> \$value\n     */", DocComments::of($method));
    }

    public function testReturnsFalseForAMethodWithoutADocComment(): void
    {
        $this->assertFalse(DocComments::of(new ReflectionMethod(ClassWithDocComments::class, 'methodWithoutDocComment')));
    }

    public function testReadsTheDocCommentOfAMethodFromTheSourceFileThatDeclaresIt(): void
    {
        $method = new ReflectionMethod(ClassWithDocComments::class, 'methodWithDocComment');

        $this->assertSame($method->getDocComment(), DocComments::inSourceFile($method));
    }

    public function testAMethodWithoutADocCommentHasNoneInTheSourceFileThatDeclaresIt(): void
    {
        $this->assertNull(DocComments::inSourceFile(new ReflectionMethod(ClassWithDocComments::class, 'methodWithoutDocComment')));
    }

    public function testReadsTheDocCommentOfAMethodOfATraitFromTheSourceFileOfTheTrait(): void
    {
        $method = new ReflectionMethod(ClassThatUsesTraitWithDocComment::class, 'methodFromTrait');

        $this->assertSame($method->getDocComment(), DocComments::inSourceFile($method));
    }

    public function testDistinguishesAMethodOfAnAnonymousClassFromTheMethodOfTheSameNameThatDeclaresIt(): void
    {
        $method = new ReflectionMethod(new ClassWithDocComments()->method(), 'method');

        $this->assertSame($method->getDocComment(), DocComments::inSourceFile($method));
        $this->assertNull(DocComments::inSourceFile(new ReflectionMethod(ClassWithDocComments::class, 'method')));
    }

    public function testAMethodThatIsNotDeclaredInAFileHasNoDocCommentInASourceFile(): void
    {
        $this->assertNull(DocComments::inSourceFile(new ReflectionMethod(ArrayObject::class, 'count')));
        $this->assertNull(DocComments::inSourceFile(new ReflectionMethod(eval('return new class { /** @param int $value */ public function method(int $value): void {} };'), 'method')));
    }

    #[RequiresSetting('opcache.save_comments', '1')]
    public function testDocCommentsAreNotDiscardedWhenOpcacheSavesThem(): void
    {
        $this->assertFalse(DocComments::areDiscarded());
    }
}
