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

namespace Ergebnis\PHPStan\Rules\Files;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Node\FileNode;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<FileNode>
 */
final class DeclareStrictTypesRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        $nodes = $node->getNodes();

        if (0 === \count($nodes)) {
            return [];
        }

        $firstNode = \array_shift($nodes);

        if (
            $firstNode instanceof Node\Stmt\InlineHTML
            && 2 === $firstNode->getEndLine()
            && 0 === \mb_strpos($firstNode->value, '#!')
        ) {
            $firstNode = \array_shift($nodes);
        }

        if ($firstNode instanceof Node\Stmt\Declare_) {
            foreach ($firstNode->declares as $declare) {
                if (
                    'strict_types' === $declare->key->toLowerString()
                    && $declare->value instanceof Node\Scalar\LNumber
                    && 1 === $declare->value->value
                ) {
                    return [];
                }
            }
        }

        return [
            Rules\RuleErrorBuilder::message('File is missing a "declare(strict_types=1)" declaration.')
                ->identifier(ErrorIdentifier::declareStrictTypes()->toString())
                ->build(),
        ];
    }
}
