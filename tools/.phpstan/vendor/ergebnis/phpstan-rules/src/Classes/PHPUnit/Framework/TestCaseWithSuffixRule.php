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

namespace Ergebnis\PHPStan\Rules\Classes\PHPUnit\Framework;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Class_>
 */
final class TestCaseWithSuffixRule implements Rules\Rule
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
                ->build(),
        ];
    }
}
