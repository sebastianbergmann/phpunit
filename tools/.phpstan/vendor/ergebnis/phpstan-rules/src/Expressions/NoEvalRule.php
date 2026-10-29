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

namespace Ergebnis\PHPStan\Rules\Expressions;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\Eval_>
 */
final class NoEvalRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct()
    {
        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noEval'),
            Documentation\Description::fromString('This rule reports an error when the language construct [`eval()`](https://www.php.net/eval) is used.'),
            Documentation\Rationale::fromString('The language construct `eval()` executes a string as code, which is a security risk and cannot be analyzed statically.'),
            Documentation\Tip::fromString('Write the code directly instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $sum = eval('return 1 + 2;');
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $sum = 1 + 2;
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
        return Node\Expr\Eval_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        return [
            Rules\RuleErrorBuilder::message('Language construct eval() should not be used.')
                ->identifier(ErrorIdentifier::noEval()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
