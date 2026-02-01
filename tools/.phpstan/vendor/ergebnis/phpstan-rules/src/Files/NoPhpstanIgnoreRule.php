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

namespace Ergebnis\PHPStan\Rules\Files;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PHPStan\Analyser;
use PHPStan\Node\FileNode;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<FileNode>
 */
final class NoPhpstanIgnoreRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        $nodeFinder = new NodeFinder();

        $foundNodes = $nodeFinder->find($node->getNodes(), static function (Node $node): bool {
            return true;
        });

        $errors = [];

        foreach ($foundNodes as $foundNode) {
            foreach ($foundNode->getComments() as $comment) {
                foreach (\explode("\n", $comment->getText()) as $index => $line) {
                    if (0 === \preg_match('/@phpstan-ignore(-line|-next-line)?(?=\s|$|,)/', $line, $matches)) {
                        continue;
                    }

                    $tag = $matches[0];

                    $message = \sprintf(
                        'Errors reported by phpstan/phpstan should not be ignored via "%s", fix the error or use the baseline instead.',
                        $tag,
                    );

                    $errors[] = Rules\RuleErrorBuilder::message($message)
                        ->identifier(ErrorIdentifier::noPhpstanIgnore()->toString())
                        ->line($comment->getStartLine() + $index)
                        ->nonIgnorable()
                        ->build();
                }
            }
        }

        return $errors;
    }
}
