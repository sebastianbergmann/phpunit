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

namespace Ergebnis\PHPStan\Rules\Expressions;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\FuncCall>
 */
final class NoCompactRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (!$node->name instanceof Node\Name) {
            return [];
        }

        if ('compact' !== \mb_strtolower($scope->resolveName($node->name))) {
            return [];
        }

        return [
            Rules\RuleErrorBuilder::message('Function compact() should not be used.')
                ->identifier(ErrorIdentifier::noCompact()->toString())
                ->build(),
        ];
    }
}
