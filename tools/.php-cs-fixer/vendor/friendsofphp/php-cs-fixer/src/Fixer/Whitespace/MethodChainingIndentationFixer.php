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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Vladimir Boliev <voff.web@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class MethodChainingIndentationFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Method chaining MUST be properly indented. Method chaining with different levels of indentation is not supported.',
            [new CodeSample("<?php\n\$user->setEmail('voff.web@gmail.com')\n         ->setPassword('233434');\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoSpaceAroundDoubleColonFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getObjectOperatorKinds());
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $lineEnding = $this->whitespacesConfig->getLineEnding();

        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isObjectOperator()) {
                continue;
            }

            $endParenthesisIndex = $tokens->getNextTokenOfKind($index, ['(', ';', ',', [\T_CLOSE_TAG]]);
            $previousEndParenthesisIndex = $tokens->getPrevTokenOfKind($index, [')']);

            if (
                null === $endParenthesisIndex
                || !$tokens[$endParenthesisIndex]->equals('(') && null === $previousEndParenthesisIndex
            ) {
                continue;
            }

            if ($this->canBeMovedToNextLine($index, $tokens)) {
                $newline = new Token([\T_WHITESPACE, $lineEnding]);

                if ($tokens[$index - 1]->isWhitespace()) {
                    $tokens[$index - 1] = $newline;
                } else {
                    $tokens->insertAt($index, $newline);
                    ++$index;
                    ++$endParenthesisIndex;
                }
            }

            $currentIndent = $this->getIndentAt($tokens, $index - 1);

            if (null === $currentIndent) {
                continue;
            }

            $expectedIndent = $this->getExpectedIndentAt($tokens, $index);

            if ($currentIndent !== $expectedIndent) {
                $tokens[$index - 1] = new Token([\T_WHITESPACE, $lineEnding.$expectedIndent]);
            }

            if (!$tokens[$endParenthesisIndex]->equals('(')) {
                continue;
            }

            $endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $endParenthesisIndex);

            for ($searchIndex = $index + 1; $searchIndex < $endParenthesisIndex; ++$searchIndex) {
                $searchToken = $tokens[$searchIndex];

                if (!$searchToken->isWhitespace()) {
                    continue;
                }

                $content = $searchToken->getContent();

                if (!Preg::match('/\R/', $content)) {
                    continue;
                }

                $content = Preg::replace(
                    '/(\R)'.$currentIndent.'(\h*)$/D',
                    '$1'.$expectedIndent.'$2',
                    $content,
                );

                $tokens[$searchIndex] = new Token([$searchToken->getId(), $content]);
            }
        }
    }

    /**
     * @param int $index index of the first token on the line to indent
     */
    private function getExpectedIndentAt(Tokens $tokens, int $index): string
    {
        $index = $tokens->getPrevMeaningfulToken($index);
        $indent = $this->whitespacesConfig->getIndent();

        for ($i = $index; $i >= 0; --$i) {
            if ($tokens[$i]->equals(')')) {
                $i = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $i);
            }

            $currentIndent = $this->getIndentAt($tokens, $i);
            if (null === $currentIndent) {
                continue;
            }

            if ($this->currentLineRequiresExtraIndentLevel($tokens, $i, $index)) {
                return $currentIndent.$indent;
            }

            return $currentIndent;
        }

        return $indent;
    }

    /**
     * @param int $index position of the object operator token ("->" or "?->")
     */
    private function canBeMovedToNextLine(int $index, Tokens $tokens): bool
    {
        $prevMeaningful = $tokens->getPrevMeaningfulToken($index);
        $hasCommentBefore = false;

        for ($i = $index - 1; $i > $prevMeaningful; --$i) {
            if ($tokens[$i]->isComment()) {
                $hasCommentBefore = true;

                continue;
            }

            if ($tokens[$i]->isWhitespace() && Preg::match('/\R/', $tokens[$i]->getContent())) {
                return $hasCommentBefore;
            }
        }

        return false;
    }

    /**
     * @param int $index index of the indentation token
     */
    private function getIndentAt(Tokens $tokens, int $index): ?string
    {
        if (Preg::match('/\R{1}(\h*)$/', $this->getIndentContentAt($tokens, $index), $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getIndentContentAt(Tokens $tokens, int $index): string
    {
        if (!$tokens[$index]->isGivenKind([\T_WHITESPACE, \T_INLINE_HTML])) {
            return '';
        }

        $content = $tokens[$index]->getContent();

        if ($tokens[$index]->isWhitespace() && $tokens[$index - 1]->isGivenKind(\T_OPEN_TAG)) {
            $content = $tokens[$index - 1]->getContent().$content;
        }

        if (Preg::match('/\R/', $content)) {
            return $content;
        }

        return '';
    }

    /**
     * @param int $start index of first meaningful token on previous line
     * @param int $end   index of last token on previous line
     */
    private function currentLineRequiresExtraIndentLevel(Tokens $tokens, int $start, int $end): bool
    {
        $firstMeaningful = $tokens->getNextMeaningfulToken($start);

        if ($tokens[$firstMeaningful]->isObjectOperator()) {
            $thirdMeaningful = $tokens->getNextMeaningfulToken($tokens->getNextMeaningfulToken($firstMeaningful));

            return
                $tokens[$thirdMeaningful]->equals('(')
                && $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $thirdMeaningful) > $end;
        }

        return
            !$tokens[$end]->equals(')')
            || $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $end) >= $start;
    }
}
