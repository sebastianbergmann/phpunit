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

namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class GetClassToClassKeywordFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Replace `get_class` calls on object variables with class keyword syntax.',
            [
                new VersionSpecificCodeSample(
                    "<?php\nget_class(\$a);\n",
                    new VersionSpecification(8_00_00),
                ),
                new VersionSpecificCodeSample(
                    "<?php\n\n\$date = new \\DateTimeImmutable();\n\$class = get_class(\$date);\n",
                    new VersionSpecification(8_00_00),
                ),
            ],
            null,
            'Risky if the `get_class` function is overridden.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before MultilineWhitespaceBeforeSemicolonsFixer.
     * Must run after NoSpacesAfterFunctionNameFixer, NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \PHP_VERSION_ID >= 8_00_00 && $tokens->isAllTokenKindsFound([\T_STRING, \T_VARIABLE]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();
        $indicesToClear = [];
        $tokenSlices = [];

        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->equals([\T_STRING, 'get_class'], false)) {
                continue;
            }

            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            $braceOpenIndex = $tokens->getNextMeaningfulToken($index);
            $braceCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $braceOpenIndex);

            if ($braceCloseIndex === $tokens->getNextMeaningfulToken($braceOpenIndex)) {
                continue; // get_class with no arguments
            }

            $meaningfulTokensCount = 0;
            $variableTokensIndices = [];

            for ($i = $braceOpenIndex + 1; $i < $braceCloseIndex; ++$i) {
                if (!$tokens[$i]->equalsAny([[\T_WHITESPACE], [\T_COMMENT], [\T_DOC_COMMENT], '(', ')'])) {
                    ++$meaningfulTokensCount;
                }

                if (!$tokens[$i]->isGivenKind(\T_VARIABLE)) {
                    continue;
                }

                if ('$this' === strtolower($tokens[$i]->getContent())) {
                    continue 2; // get_class($this)
                }

                $variableTokensIndices[] = $i;
            }

            if ($meaningfulTokensCount > 1 || 1 !== \count($variableTokensIndices)) {
                continue; // argument contains more logic, or more arguments, or no variable argument
            }

            $indicesToClear[$index] = [$braceOpenIndex, current($variableTokensIndices), $braceCloseIndex];
        }

        foreach ($indicesToClear as $index => $items) {
            $tokenSlices[$index] = $this->getReplacementTokenSlices($tokens, $items[1]);
            $this->clearGetClassCall($tokens, $index, $items[0], $items[2]);
        }

        $tokens->insertSlices($tokenSlices);
    }

    /**
     * @return non-empty-list<Token>
     */
    private function getReplacementTokenSlices(Tokens $tokens, int $variableIndex): array
    {
        return [
            new Token([\T_VARIABLE, $tokens[$variableIndex]->getContent()]),
            new Token([\T_DOUBLE_COLON, '::']),
            new Token([CT::T_CLASS_CONSTANT, 'class']),
        ];
    }

    private function clearGetClassCall(Tokens $tokens, int $index, int $braceOpenIndex, int $braceCloseIndex): void
    {
        for ($i = $braceOpenIndex; $i <= $braceCloseIndex; ++$i) {
            if ($tokens[$i]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $tokens->clearAt($prevIndex);
        }

        $tokens->clearAt($index);
    }
}
