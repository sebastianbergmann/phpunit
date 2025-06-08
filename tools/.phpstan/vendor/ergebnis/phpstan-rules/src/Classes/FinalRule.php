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

namespace Ergebnis\PHPStan\Rules\Classes;

use Doctrine\ORM;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Comment;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Class_>
 */
final class FinalRule implements Rules\Rule
{
    /**
     * @var list<string>
     */
    private static array $whitelistedAnnotations = [
        'Entity',
        'ORM\Entity',
        'ORM\Mapping\Entity',
    ];

    /**
     * @var list<class-string>
     */
    private static array $whitelistedAttributes = [
        ORM\Mapping\Entity::class,
    ];
    private bool $allowAbstractClasses;

    /**
     * @var list<string>
     */
    private array $classesNotRequiredToBeAbstractOrFinal;
    private string $errorMessageTemplate = 'Class %s is not final.';

    /**
     * @param list<class-string> $classesNotRequiredToBeAbstractOrFinal
     */
    public function __construct(
        bool $allowAbstractClasses,
        array $classesNotRequiredToBeAbstractOrFinal
    ) {
        $this->allowAbstractClasses = $allowAbstractClasses;
        $this->classesNotRequiredToBeAbstractOrFinal = \array_map(static function (string $classNotRequiredToBeAbstractOrFinal): string {
            return $classNotRequiredToBeAbstractOrFinal;
        }, $classesNotRequiredToBeAbstractOrFinal);

        if ($allowAbstractClasses) {
            $this->errorMessageTemplate = 'Class %s is neither abstract nor final.';
        }
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (!isset($node->namespacedName)) {
            return [];
        }

        if (\in_array($node->namespacedName->toString(), $this->classesNotRequiredToBeAbstractOrFinal, true)) {
            return [];
        }

        if (
            $this->allowAbstractClasses
            && $node->isAbstract()
        ) {
            return [];
        }

        if ($node->isFinal()) {
            return [];
        }

        if ($this->hasWhitelistedAnnotation($node)) {
            return [];
        }

        if ($this->hasWhitelistedAttribute($node)) {
            return [];
        }

        $message = \sprintf(
            $this->errorMessageTemplate,
            $node->namespacedName->toString(),
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::final()->toString())
                ->build(),
        ];
    }

    /**
     * This method is inspired by the work on PhpCsFixer\Fixer\ClassNotation\FinalClassFixer and
     * PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer contributed by Dariusz Rumiński, Filippo Tessarotto, and
     * Spacepossum for friendsofphp/php-cs-fixer.
     *
     * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalClassFixer.php
     * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalInternalClassFixer.php
     * @see https://github.com/keradus
     * @see https://github.com/SpacePossum
     * @see https://github.com/Slamdunk
     */
    private function hasWhitelistedAnnotation(Node\Stmt\Class_ $node): bool
    {
        $docComment = $node->getDocComment();

        if (!$docComment instanceof Comment\Doc) {
            return false;
        }

        $reformattedComment = $docComment->getReformattedText();

        if (\is_int(\preg_match_all('/@(\S+)(?=\s|$)/', $reformattedComment, $matches))) {
            foreach ($matches[1] as $annotation) {
                foreach (self::$whitelistedAnnotations as $whitelistedAnnotation) {
                    if (0 === \mb_strpos($annotation, $whitelistedAnnotation)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function hasWhitelistedAttribute(Node\Stmt\Class_ $node): bool
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
}
