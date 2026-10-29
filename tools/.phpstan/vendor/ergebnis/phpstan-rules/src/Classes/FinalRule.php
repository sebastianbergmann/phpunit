<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Classes;

use Doctrine\ORM;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Comment;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Class_>
 */
final class FinalRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
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
    private bool $ignoreDoctrineEntities;

    /**
     * @var list<string>
     */
    private array $classesNotRequiredToBeAbstractOrFinal;
    private string $errorMessageTemplate = 'Class %s is not final.';
    private Documentation\RuleDefinition $ruleDefinition;

    /**
     * @param list<class-string> $classesNotRequiredToBeAbstractOrFinal
     */
    public function __construct(
        bool $allowAbstractClasses,
        array $classesNotRequiredToBeAbstractOrFinal,
        bool $ignoreDoctrineEntities
    ) {
        $this->allowAbstractClasses = $allowAbstractClasses;
        $this->ignoreDoctrineEntities = $ignoreDoctrineEntities;
        $this->classesNotRequiredToBeAbstractOrFinal = \array_map(static function (string $classNotRequiredToBeAbstractOrFinal): string {
            return $classNotRequiredToBeAbstractOrFinal;
        }, $classesNotRequiredToBeAbstractOrFinal);

        if ($allowAbstractClasses) {
            $this->errorMessageTemplate = 'Class %s is neither abstract nor final.';
        }

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('final'),
            Documentation\Description::fromString(<<<'MARKDOWN'
            This rule reports an error when a non-anonymous class is not `final`.

            💡 By default, this rule ignores classes that

            - use `@Entity`, `@ORM\Entity`, or `@ORM\Mapping\Entity` annotations
            - use `Doctrine\ORM\Mapping\Entity` attributes

            on the class level.
            MARKDOWN),
            Documentation\Rationale::fromString('A class that is not `final` can be extended, so every change to it can break a subclass. Declare classes `final` and prefer composition over inheritance.'),
            Documentation\Tip::fromString('Declare the class `final` and prefer composition over inheritance.'),
            Documentation\ParameterList::create(
                Documentation\Parameter::create(
                    Documentation\ParameterName::fromString('allowAbstractClasses'),
                    Documentation\Description::fromString(<<<'MARKDOWN'
                    By default, this rule allows to declare `abstract` classes.

                    You can set the `allowAbstractClasses` parameter to `false` to disallow abstract classes.
                    MARKDOWN),
                    Documentation\ParameterExample::fromBool(false),
                ),
                Documentation\Parameter::create(
                    Documentation\ParameterName::fromString('classesNotRequiredToBeAbstractOrFinal'),
                    Documentation\Description::fromString('You can set the `classesNotRequiredToBeAbstractOrFinal` parameter to a list of class names that you want to exclude from inspection.'),
                    Documentation\ParameterExample::fromStrings(
                        'Foo\\Bar\\NeitherAbstractNorFinal',
                        'Bar\\Baz\\NeitherAbstractNorFinal',
                    ),
                ),
                Documentation\Parameter::create(
                    Documentation\ParameterName::fromString('ignoreDoctrineEntities'),
                    Documentation\Description::fromString(<<<'MARKDOWN'
                    By default, this rule ignores Doctrine entities, because Doctrine creates proxies for lazy loading by extending them.

                    When you use [native lazy objects](https://github.com/doctrine/orm/pull/11853) with `doctrine/orm:^3.4.0` on PHP 8.4 or later, Doctrine no longer extends entities, and you can set the `ignoreDoctrineEntities` parameter to `false` to require Doctrine entities to be `final` as well.
                    MARKDOWN),
                    Documentation\ParameterExample::fromBool(false),
                ),
            ),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    class Greeting
                    {
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Greeting
                    {
                    }
                    PHP),
            ),
        );
    }

    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return $this->ruleDefinition;
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

        if (
            $this->ignoreDoctrineEntities
            && self::hasWhitelistedAnnotation($node)
        ) {
            return [];
        }

        if (
            $this->ignoreDoctrineEntities
            && self::hasWhitelistedAttribute($node)
        ) {
            return [];
        }

        $message = \sprintf(
            $this->errorMessageTemplate,
            $node->namespacedName->toString(),
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::final()->toString())
                ->tip(Documentation\ErrorTip::for($this))
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
    private static function hasWhitelistedAnnotation(Node\Stmt\Class_ $node): bool
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

    private static function hasWhitelistedAttribute(Node\Stmt\Class_ $node): bool
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
