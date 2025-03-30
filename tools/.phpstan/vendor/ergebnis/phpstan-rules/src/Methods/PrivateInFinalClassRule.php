<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Methods;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Comment;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;
use PHPStan\Type;
use PHPUnit\Framework;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class PrivateInFinalClassRule implements Rules\Rule
{
    /**
     * @var list<string>
     */
    private static array $whitelistedAnnotations = [
        '@after',
        '@before',
        '@postCondition',
        '@preCondition',
    ];

    /**
     * @var list<class-string>
     */
    private static array $whitelistedAttributes = [
        Framework\Attributes\After::class,
        Framework\Attributes\Before::class,
        Framework\Attributes\PostCondition::class,
        Framework\Attributes\PreCondition::class,
    ];
    private Type\FileTypeMapper $fileTypeMapper;

    public function __construct(Type\FileTypeMapper $fileTypeMapper)
    {
        $this->fileTypeMapper = $fileTypeMapper;
    }

    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        /** @var Reflection\ClassReflection $containingClass */
        $containingClass = $scope->getClassReflection();

        if (!$containingClass->isFinal()) {
            return [];
        }

        if ($node->isPublic()) {
            return [];
        }

        if ($node->isPrivate()) {
            return [];
        }

        if ($this->hasWhitelistedAnnotation($node, $containingClass)) {
            return [];
        }

        if (self::hasWhitelistedAttribute($node)) {
            return [];
        }

        $methodName = $node->name->toString();

        if (self::isDeclaredByParentClass($containingClass, $methodName)) {
            return [];
        }

        if (self::isDeclaredByTrait($containingClass, $methodName)) {
            return [];
        }

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            $message = \sprintf(
                'Method %s() in anonymous class is protected, but since the containing class is final, it can be private.',
                $node->name->name,
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::privateInFinalClass()->toString())
                    ->build(),
            ];
        }

        $message = \sprintf(
            'Method %s::%s() is protected, but since the containing class is final, it can be private.',
            $containingClass->getName(),
            $methodName,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::privateInFinalClass()->toString())
                ->build(),
        ];
    }

    private function hasWhitelistedAnnotation(
        Node\Stmt\ClassMethod $node,
        Reflection\ClassReflection $containingClass
    ): bool {
        $docComment = $node->getDocComment();

        if (!$docComment instanceof Comment\Doc) {
            return false;
        }

        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
            null,
            $containingClass->getName(),
            null,
            null,
            $docComment->getText(),
        );

        foreach ($resolvedPhpDoc->getPhpDocNodes() as $phpDocNode) {
            foreach ($phpDocNode->getTags() as $tag) {
                if (\in_array($tag->name, self::$whitelistedAnnotations, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function hasWhitelistedAttribute(Node\Stmt\ClassMethod $node): bool
    {
        foreach ($node->attrGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if (\in_array($attribute->name->toString(), self::$whitelistedAttributes, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function isDeclaredByParentClass(
        Reflection\ClassReflection $containingClass,
        string $methodName
    ): bool {
        $parentClass = $containingClass->getNativeReflection()->getParentClass();

        if (!$parentClass instanceof \ReflectionClass) {
            return false;
        }

        if (!$parentClass->hasMethod($methodName)) {
            return false;
        }

        return true;
    }

    private static function isDeclaredByTrait(
        Reflection\ClassReflection $containingClass,
        string $methodName
    ): bool {
        foreach ($containingClass->getTraits() as $trait) {
            if ($trait->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
