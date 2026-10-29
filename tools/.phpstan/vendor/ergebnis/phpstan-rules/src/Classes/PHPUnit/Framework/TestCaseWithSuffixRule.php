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

namespace Ergebnis\PHPStan\Rules\Classes\PHPUnit\Framework;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Class_>
 */
final class TestCaseWithSuffixRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    /**
     * @var list<class-string>
     */
    private static array $phpunitTestCaseClassNames = [
        'PHPUnit\Framework\TestCase',
    ];
    private Reflection\ReflectionProvider $reflectionProvider;

    public function __construct(Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'testCaseWithSuffix',
            'This rule reports an error when a concrete class is a sub-class of `PHPUnit\\Framework\\TestCase` but does not have a `Test` suffix.',
            'By default, PHPUnit only runs tests from files whose names end with `Test.php`, and the suffix tells readers which classes are tests.',
            'Add the `Test` suffix to the name of the class.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example\Test;

                use PHPUnit\Framework;

                final class Greeting extends Framework\TestCase
                {
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example\Test;

                use PHPUnit\Framework;

                final class GreetingTest extends Framework\TestCase
                {
                }
                PHP),
        );
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if ($node->isAbstract()) {
            return [];
        }

        if (!$node->extends instanceof Node\Name) {
            return [];
        }

        if (!isset($node->namespacedName)) {
            return [];
        }

        $fullyQualifiedClassName = $node->namespacedName->toString();

        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedClassName);

        $extendedPhpunitTestCaseClassName = '';

        foreach (self::$phpunitTestCaseClassNames as $phpunitTestCaseClassName) {
            if ($classReflection->isSubclassOfClass($this->reflectionProvider->getClass($phpunitTestCaseClassName))) {
                $extendedPhpunitTestCaseClassName = $phpunitTestCaseClassName;

                break;
            }
        }

        if ('' === $extendedPhpunitTestCaseClassName) {
            return [];
        }

        if (1 === \preg_match('/Test$/', $fullyQualifiedClassName)) {
            return [];
        }

        $message = \sprintf(
            'Class %s extends %s, is concrete, but does not have a Test suffix.',
            $fullyQualifiedClassName,
            $extendedPhpunitTestCaseClassName,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::testCaseWithSuffix()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
