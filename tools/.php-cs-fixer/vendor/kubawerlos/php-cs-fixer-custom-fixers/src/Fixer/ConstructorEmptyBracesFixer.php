<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\ConstructorAnalyzer;

/**
 * @no-named-arguments
 */
final class ConstructorEmptyBracesFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Constructor\'s empty braces must be on a single line.',
            [
                new CodeSample(
                    '<?php
class Foo {
    public function __construct(
        $param1,
        $param2
    ) {
    }
}
',
                ),
            ],
            '',
        );
    }

    /**
     * Must run after BracesPositionFixer, PromotedConstructorPropertyFixer.
     */
    public function getPriority(): int
    {
        return -3;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('{');
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $constructorAnalyzer = new ConstructorAnalyzer();

        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $constructorAnalysis = $constructorAnalyzer->findNonAbstractConstructor($tokens, $index);
            if ($constructorAnalysis === null) {
                continue;
            }

            $openParenthesisIndex = $tokens->getNextTokenOfKind($constructorAnalysis->getConstructorIndex(), ['(']);
            \assert(\is_int($openParenthesisIndex));

            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);

            $openBraceIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
            \assert(\is_int($openBraceIndex));

            $closeBraceIndex = $tokens->getNextNonWhitespace($openBraceIndex);
            \assert(\is_int($closeBraceIndex));
            if (!$tokens[$closeBraceIndex]->equals('}')) {
                continue;
            }

            $tokens->ensureWhitespaceAtIndex($openBraceIndex + 1, 0, '');
            $tokens->ensureWhitespaceAtIndex($closeParenthesisIndex + 1, 0, ' ');
        }
    }
}
