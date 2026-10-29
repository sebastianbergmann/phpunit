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

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;
use PHPUnit\Framework;

/**
 * @implements Rules\Rule<Node\Stmt\Class_>
 */
final class NoExtendsRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    /**
     * @var list<class-string>
     */
    private static array $defaultClassesAllowedToBeExtended = [
        Framework\TestCase::class,
    ];

    /**
     * @var list<class-string>
     */
    private array $classesAllowedToBeExtended;
    private Documentation\RuleDefinition $ruleDefinition;

    /**
     * @param list<class-string> $classesAllowedToBeExtended
     */
    public function __construct(array $classesAllowedToBeExtended)
    {
        $this->classesAllowedToBeExtended = \array_values(\array_unique(\array_merge(
            self::$defaultClassesAllowedToBeExtended,
            \array_map(static function (string $classAllowedToBeExtended): string {
                /** @var class-string $classAllowedToBeExtended */
                return $classAllowedToBeExtended;
            }, $classesAllowedToBeExtended),
        )));

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noExtends'),
            Documentation\Description::fromString('This rule reports an error when a class extends another class.'),
            Documentation\Rationale::fromString('Extending a class couples the subclass to the implementation of its parent class. Prefer composition: inject collaborators and implement interfaces.'),
            Documentation\Tip::fromString('Inject collaborators and implement interfaces instead.'),
            Documentation\ParameterList::create(Documentation\Parameter::create(
                Documentation\ParameterName::fromString('classesAllowedToBeExtended'),
                Documentation\Description::fromString(<<<'MARKDOWN'
                By default, this rule allows the following classes to be extended:

                - [`PHPUnit\Framework\TestCase`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php)

                You can set the `classesAllowedToBeExtended` parameter to a list of class names that you want to allow to be extended.
                MARKDOWN),
                Documentation\ParameterExample::fromStrings(
                    'Ergebnis\\PHPStan\\Rules\\Test\\Integration\\AbstractTestCase',
                    'PHPStan\\Testing\\RuleTestCase',
                ),
            )),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    abstract class AbstractGreeting
                    {
                    }

                    final class Greeting extends AbstractGreeting
                    {
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    interface Greeting
                    {
                    }

                    final class FriendlyGreeting implements Greeting
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
        if (!$node->extends instanceof Node\Name) {
            return [];
        }

        $extendedClassName = $node->extends->toString();

        if (\in_array($extendedClassName, $this->classesAllowedToBeExtended, true)) {
            return [];
        }

        if (!isset($node->namespacedName)) {
            $message = \sprintf(
                'Anonymous class is not allowed to extend "%s".',
                $extendedClassName,
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noExtends()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        $extendingClassName = $node->namespacedName->toString();

        $message = \sprintf(
            'Class "%s" is not allowed to extend "%s".',
            $extendingClassName,
            $extendedClassName,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noExtends()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
