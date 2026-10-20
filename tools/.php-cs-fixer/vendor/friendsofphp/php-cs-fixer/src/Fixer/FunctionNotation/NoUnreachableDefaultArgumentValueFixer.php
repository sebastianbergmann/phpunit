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

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Mark Scherer
 * @author Lucas Manzke <lmanzke@outlook.com>
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUnreachableDefaultArgumentValueFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'In function arguments there must not be arguments with default values before non-default ones.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        function example($foo = "two words", $bar) {}

                        PHP,
                ),
            ],
            null,
            'Modifies the signature of functions; therefore risky when using systems (such as some Symfony components) that rely on those (for example through reflection).',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NullableTypeDeclarationForDefaultNullValueFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_FUNCTION, \T_FN]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionKinds = [\T_FUNCTION, \T_FN];

        for ($i = 0, $l = $tokens->count(); $i < $l; ++$i) {
            if (!$tokens[$i]->isGivenKind($functionKinds)) {
                continue;
            }

            $this->fixFunctionDefinition($tokens, $i);
        }
    }

    private function fixFunctionDefinition(Tokens $tokens, int $startIndex): void
    {
        $removeDefaultArgument = false;

        foreach (array_reverse((new FunctionsAnalyzer())->getFunctionArguments($tokens, $startIndex)) as $argumentAnalysis) {
            $prevVariableIndex = $tokens->getPrevMeaningfulToken($argumentAnalysis->getNameIndex());
            if ($tokens[$prevVariableIndex]->isGivenKind(\T_ELLIPSIS)) {
                continue;
            }

            if (null === $argumentAnalysis->getDefault()) {
                $removeDefaultArgument = true;

                continue;
            }

            if (!$removeDefaultArgument) {
                continue;
            }

            if (
                'null' === strtolower($argumentAnalysis->getDefault())
                && $argumentAnalysis->hasTypeAnalysis()
                && !$argumentAnalysis->getTypeAnalysis()->isNullable()
            ) {
                continue;
            }

            $this->removeDefaultValue(
                $tokens,
                $argumentAnalysis->getNameIndex(),
                $this->getDefaultValueEnd($tokens, $argumentAnalysis->getNameIndex()),
            );
        }
    }

    private function removeDefaultValue(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        for ($i = $tokens->getNextMeaningfulToken($startIndex); $i <= $endIndex;) {
            $this->clearWhitespacesBeforeIndex($tokens, $i);
            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
            $i = $tokens->getNextMeaningfulToken($i);
        }
    }

    private function getDefaultValueEnd(Tokens $tokens, int $index): int
    {
        while (null !== $index = $tokens->getNextMeaningfulToken($index)) {
            if ($tokens[$index]->equalsAny([',', [CT::T_PROPERTY_HOOK_BRACE_OPEN]])) {
                break;
            }

            $blockType = Tokens::detectBlockType($tokens[$index]);
            if (null !== $blockType && $blockType['isStart']) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);
            }
        }

        return $tokens->getPrevMeaningfulToken($index);
    }

    private function clearWhitespacesBeforeIndex(Tokens $tokens, int $index): void
    {
        $prevIndex = $tokens->getNonEmptySibling($index, -1);
        if (!$tokens[$prevIndex]->isWhitespace()) {
            return;
        }

        $prevNonWhiteIndex = $tokens->getPrevNonWhitespace($prevIndex);
        if (null === $prevNonWhiteIndex || !$tokens[$prevNonWhiteIndex]->isComment()) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
        }
    }
}
