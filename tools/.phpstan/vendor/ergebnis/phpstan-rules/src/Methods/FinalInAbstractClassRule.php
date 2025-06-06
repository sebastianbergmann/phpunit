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

use Doctrine\ORM;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\PhpDoc;
use PHPStan\PhpDocParser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class FinalInAbstractClassRule implements Rules\Rule
{
    private const DOCTRINE_ATTRIBUTE_NAMES = [
        ORM\Mapping\Embeddable::class,
        ORM\Mapping\Entity::class,
    ];
    private const DOCTRINE_ANNOTATION_NAMES = [
        '@ORM\\Mapping\\Embeddable',
        '@ORM\\Embeddable',
        '@Embeddable',
        '@ORM\\Mapping\\Entity',
        '@ORM\\Entity',
        '@Entity',
    ];

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

        if (self::isDoctrineEntity($containingClass)) {
            return [];
        }

        if (!$containingClass->isAbstract()) {
            return [];
        }

        if ($containingClass->isInterface()) {
            return [];
        }

        if ($node->isAbstract()) {
            return [];
        }

        if ($node->isFinal()) {
            return [];
        }

        if ($node->isPrivate()) {
            return [];
        }

        if ('__construct' === $node->name->name) {
            return [];
        }

        $message = \sprintf(
            'Method %s::%s() is not final, but since the containing class is abstract, it should be.',
            $containingClass->getName(),
            $node->name->toString(),
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::finalInAbstractClass()->toString())
                ->build(),
        ];
    }

    private static function isDoctrineEntity(Reflection\ClassReflection $containingClass): bool
    {
        $attributes = $containingClass->getNativeReflection()->getAttributes();

        foreach ($attributes as $attribute) {
            if (\in_array($attribute->getName(), self::DOCTRINE_ATTRIBUTE_NAMES, true)) {
                return true;
            }
        }

        $resolvedPhpDocBlock = $containingClass->getResolvedPhpDoc();

        if ($resolvedPhpDocBlock instanceof PhpDoc\ResolvedPhpDocBlock) {
            foreach ($resolvedPhpDocBlock->getPhpDocNodes() as $phpDocNode) {
                foreach ($phpDocNode->children as $child) {
                    if (!$child instanceof PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                        continue;
                    }

                    if (\in_array($child->name, self::DOCTRINE_ANNOTATION_NAMES, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
