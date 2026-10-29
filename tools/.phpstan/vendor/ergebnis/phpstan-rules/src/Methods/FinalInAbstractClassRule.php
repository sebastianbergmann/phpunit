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

namespace Ergebnis\PHPStan\Rules\Methods;

use Doctrine\ORM;
use Ergebnis\PHPStan\Rules\Documentation;
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
final class FinalInAbstractClassRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private const DOCTRINE_ATTRIBUTE_NAMES = [
        ORM\Mapping\Entity::class,
    ];
    private const DOCTRINE_ANNOTATION_NAMES = [
        '@ORM\\Mapping\\Entity',
        '@ORM\\Entity',
        '@Entity',
    ];
    private bool $ignoreDoctrineEntities;
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct(bool $ignoreDoctrineEntities)
    {
        $this->ignoreDoctrineEntities = $ignoreDoctrineEntities;

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('finalInAbstractClass'),
            Documentation\Description::fromString(<<<'MARKDOWN'
            This rule reports an error when a concrete `public` or `protected` method in an `abstract` class is not `final`.

            💡 By default, this rule ignores Doctrine entities.
            MARKDOWN),
            Documentation\Rationale::fromString('A concrete method in an `abstract` class that is not `final` can be overridden by subclasses, which can break the assumptions of the abstract class. Declare the method `final`, or `abstract` when subclasses need to provide it.'),
            Documentation\Tip::fromString('Declare the method `final`, or `abstract` when subclasses need to provide it.'),
            Documentation\ParameterList::create(Documentation\Parameter::create(
                Documentation\ParameterName::fromString('ignoreDoctrineEntities'),
                Documentation\Description::fromString(<<<'MARKDOWN'
                By default, this rule ignores Doctrine entities, because Doctrine creates proxies for lazy loading by extending them.

                When you use [native lazy objects](https://github.com/doctrine/orm/pull/11853) with `doctrine/orm:^3.4.0` on PHP 8.4 or later, Doctrine no longer extends entities, and you can set the `ignoreDoctrineEntities` parameter to `false` to require concrete methods in `abstract` Doctrine entities to be `final` as well.
                MARKDOWN),
                Documentation\ParameterExample::fromBool(false),
            )),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    abstract class AbstractGreeting
                    {
                        public function greet(): string
                        {
                            return 'Hello!';
                        }
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    abstract class AbstractGreeting
                    {
                        final public function greet(): string
                        {
                            return 'Hello!';
                        }
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
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        /** @var Reflection\ClassReflection $containingClass */
        $containingClass = $scope->getClassReflection();

        if (
            $this->ignoreDoctrineEntities
            && self::isDoctrineEntity($containingClass)
        ) {
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
                ->tip(Documentation\ErrorTip::for($this))
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
