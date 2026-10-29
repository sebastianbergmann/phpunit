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
 * @implements Rules\Rule<Node\Expr\Isset_>
 */
final class NoIssetRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noIsset',
            'This rule reports an error when the language construct [`isset()`](https://www.php.net/isset) is used.',
            'The language construct `isset()` returns `false` both when a variable, array key, or property does not exist and when it holds `null`, so it hides which of the two applies. Use `array_key_exists()`, `property_exists()`, or a comparison with `null` instead.',
            'Use `array_key_exists()`, `property_exists()`, or a comparison with `null` instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                $values = [
                    'name' => 'Jane',
                ];

                if (isset($values['name'])) {
                    echo $values['name'];
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                $values = [
                    'name' => 'Jane',
                ];

                if (\array_key_exists('name', $values)) {
                    echo $values['name'];
                }
                PHP),
        );
    }

    public function getNodeType(): string
    {
        return Node\Expr\Isset_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        return [
            Rules\RuleErrorBuilder::message('Language construct isset() should not be used.')
                ->identifier(ErrorIdentifier::noIsset()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
