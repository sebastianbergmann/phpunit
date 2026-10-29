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

namespace Ergebnis\PHPStan\Rules\Functions;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Function_>
 */
final class NoReturnByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct()
    {
        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noReturnByReference'),
            Documentation\Description::fromString('This rule reports an error when a function [returns by reference](https://www.php.net/manual/en/language.references.return.php).'),
            Documentation\Rationale::fromString('Returning by reference lets the caller change state that the function keeps, which makes the code harder to follow. Return a value instead.'),
            Documentation\Tip::fromString('Return a value instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    function &values(): array
                    {
                        static $values = [];

                        return $values;
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    function values(): array
                    {
                        return [];
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
        return Node\Stmt\Function_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (false === $node->byRef) {
            return [];
        }

        $message = \sprintf(
            'Function %s() returns by reference.',
            $node->namespacedName,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noReturnByReference()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
