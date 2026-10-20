<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\AlternativeSyntaxAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ControlStructureBracesFixer extends AbstractFixer
{
    private const CONTROL_TOKENS = [
        \T_DECLARE,
        \T_DO,
        \T_ELSE,
        \T_ELSEIF,
        \T_FINALLY,
        \T_FOR,
        \T_FOREACH,
        \T_IF,
        \T_WHILE,
        \T_TRY,
        \T_CATCH,
        \T_SWITCH,
    ];

    /**
     * Control structures that take no condition, so that a parenthesis
     * following one of them opens their body rather than closing a condition.
     */
    private const CONDITIONLESS_CONTROL_TOKENS = [
        \T_DO,
        \T_ELSE,
        \T_FINALLY,
        \T_TRY,
    ];

    private const CONTROL_CONTINUATION_TOKENS = [
        \T_IF => [\T_ELSE, \T_ELSEIF],
        \T_DO => [\T_WHILE],
        \T_TRY => [\T_CATCH, \T_FINALLY],
    ];

    private const FINAL_CONTROL_CONTINUATION_TOKENS = [
        \T_IF => [\T_ELSE],
        \T_DO => [],
        \T_TRY => [\T_FINALLY],
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The body of each control structure MUST be enclosed within braces.',
            [new CodeSample("<?php\nif (foo()) echo 'Hello!';\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BracesPositionFixer, ControlStructureContinuationPositionFixer, CurlyBracesPositionFixer, NoMultipleStatementsPerLineFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $alternativeSyntaxAnalyzer = new AlternativeSyntaxAnalyzer();

        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(self::CONTROL_TOKENS)) {
                continue;
            }

            if (
                $token->isGivenKind(\T_ELSE)
                && $tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(\T_IF)
            ) {
                continue;
            }

            $parenthesisEndIndex = $this->findParenthesisEnd($tokens, $index);
            $nextAfterParenthesisEndIndex = $tokens->getNextMeaningfulToken($parenthesisEndIndex);
            $tokenAfterParenthesis = $tokens[$nextAfterParenthesisEndIndex];

            if ($tokenAfterParenthesis->equalsAny([';', '{', ':', [\T_CLOSE_TAG]])) {
                continue;
            }

            $statementEndIndex = null;

            if ($tokenAfterParenthesis->isGivenKind([\T_IF, \T_FOR, \T_FOREACH, \T_SWITCH, \T_WHILE])) {
                $tokenAfterParenthesisBlockEnd = $tokens->findBlockEnd( // go to ')'
                    Tokens::BLOCK_TYPE_PARENTHESIS,
                    $tokens->getNextMeaningfulToken($nextAfterParenthesisEndIndex),
                );

                if ($tokens[$tokens->getNextMeaningfulToken($tokenAfterParenthesisBlockEnd)]->equals(':')) {
                    $statementEndIndex = $alternativeSyntaxAnalyzer->findAlternativeSyntaxBlockEnd($tokens, $nextAfterParenthesisEndIndex);

                    $tokenAfterStatementEndIndex = $tokens->getNextMeaningfulToken($statementEndIndex);
                    if ($tokens[$tokenAfterStatementEndIndex]->equals(';')) {
                        $statementEndIndex = $tokenAfterStatementEndIndex;
                    }
                }
            }

            if (null === $statementEndIndex) {
                $statementEndIndex = $this->findStatementEnd($tokens, $parenthesisEndIndex);
            }

            $tokensToInsertAfterStatement = [
                new Token([\T_WHITESPACE, ' ']),
                new Token('}'),
            ];

            if (!$tokens[$statementEndIndex]->equalsAny([';', '}'])) {
                array_unshift($tokensToInsertAfterStatement, new Token(';'));
            }

            $tokens->insertSlices([$statementEndIndex + 1 => $tokensToInsertAfterStatement]);

            // insert opening brace
            $tokens->insertSlices([$parenthesisEndIndex + 1 => [
                new Token([\T_WHITESPACE, ' ']),
                new Token('{'),
            ]]);
        }
    }

    private function findParenthesisEnd(Tokens $tokens, int $structureTokenIndex): int
    {
        if ($tokens[$structureTokenIndex]->isGivenKind(self::CONDITIONLESS_CONTROL_TOKENS)) {
            return $structureTokenIndex;
        }

        $nextIndex = $tokens->getNextMeaningfulToken($structureTokenIndex);
        $nextToken = $tokens[$nextIndex];

        if (!$nextToken->equals('(')) {
            return $structureTokenIndex;
        }

        return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $nextIndex);
    }

    private function findStatementEnd(Tokens $tokens, int $parenthesisEndIndex): int
    {
        $nextIndex = $tokens->getNextMeaningfulToken($parenthesisEndIndex);
        \assert(\is_int($nextIndex));

        $nextToken = $tokens[$nextIndex];

        if ($nextToken->equals('{')) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $nextIndex);
        }

        if ($nextToken->isGivenKind(self::CONTROL_TOKENS)) {
            $parenthesisEndIndex = $this->findParenthesisEnd($tokens, $nextIndex);

            $endIndex = $this->findStatementEnd($tokens, $parenthesisEndIndex);

            if ($nextToken->isGivenKind([\T_IF, \T_TRY, \T_DO])) {
                $openingTokenKind = $nextToken->getId();

                while (true) {
                    $nextIndex = $tokens->getNextMeaningfulToken($endIndex);
                    \assert(isset(self::CONTROL_CONTINUATION_TOKENS[$openingTokenKind]));
                    if (null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(self::CONTROL_CONTINUATION_TOKENS[$openingTokenKind])) {
                        $parenthesisEndIndex = $this->findParenthesisEnd($tokens, $nextIndex);

                        $endIndex = $this->findStatementEnd($tokens, $parenthesisEndIndex);

                        \assert(isset(self::FINAL_CONTROL_CONTINUATION_TOKENS[$openingTokenKind]));
                        if ($tokens[$nextIndex]->isGivenKind(self::FINAL_CONTROL_CONTINUATION_TOKENS[$openingTokenKind])) {
                            return $endIndex;
                        }
                    } else {
                        break;
                    }
                }
            }

            return $endIndex;
        }

        $index = $parenthesisEndIndex;

        while (true) {
            $token = $tokens[++$index];

            // if there is some block in statement (eg lambda function) we need to skip it
            if ($token->equals('{')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }

            if ($token->equals(';')) {
                return $index;
            }

            if ($token->isGivenKind(\T_CLOSE_TAG)) {
                return $tokens->getPrevNonWhitespace($index);
            }
        }
    }
}
