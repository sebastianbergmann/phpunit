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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @phpstan-type _UnsetInfo array{
 *     startIndex: int,
 *     endIndex: int,
 *     isToTransform: bool,
 *     isFirst: bool,
 * }
 *
 * @author Gert de Pagter <BackEndTea@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUnsetOnPropertyFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Properties should be set to `null` instead of using `unset`.',
            [new CodeSample("<?php\nunset(\$this->a);\n")],
            null,
            'Risky when relying on attributes to be removed using `unset` rather than be set to `null`.'
            .' Changing variables to `null` instead of unsetting means these still show up when looping over class variables'
            .' and reference properties remain unbroken.'
            .' Since PHP 7.4, this rule might introduce `null` assignments to properties whose type declaration does not allow it.',
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_UNSET)
            && $tokens->isAnyTokenKindsFound([\T_OBJECT_OPERATOR, \T_PAAMAYIM_NEKUDOTAYIM]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before CombineConsecutiveUnsetsFixer.
     */
    public function getPriority(): int
    {
        return 25;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_UNSET)) {
                continue;
            }

            $unsetsInfo = $this->getUnsetsInfo($tokens, $index);

            if (!$this->isAnyUnsetToTransform($unsetsInfo)) {
                continue;
            }

            $isLastUnset = true; // "last" as we reverse the array below

            foreach (array_reverse($unsetsInfo) as $unsetInfo) {
                $this->updateTokens($tokens, $unsetInfo, $isLastUnset);
                $isLastUnset = false;
            }
        }
    }

    /**
     * @return list<_UnsetInfo>
     */
    private function getUnsetsInfo(Tokens $tokens, int $index): array
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        $unsetStart = $tokens->getNextTokenOfKind($index, ['(']);
        $unsetEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $unsetStart);
        $isFirst = true;
        $unsets = [];

        foreach ($argumentsAnalyzer->getArguments($tokens, $unsetStart, $unsetEnd) as $startIndex => $endIndex) {
            $startIndex = $tokens->getNextMeaningfulToken($startIndex - 1);
            $endIndex = $tokens->getPrevMeaningfulToken($endIndex + 1);
            $unsets[] = [
                'startIndex' => $startIndex,
                'endIndex' => $endIndex,
                'isToTransform' => $this->isProperty($tokens, $startIndex, $endIndex),
                'isFirst' => $isFirst,
            ];
            $isFirst = false;
        }

        return $unsets;
    }

    private function isProperty(Tokens $tokens, int $index, int $endIndex): bool
    {
        if ($tokens[$index]->isGivenKind(\T_VARIABLE)) {
            $nextIndex = $tokens->getNextMeaningfulToken($index);

            if (null === $nextIndex || !$tokens[$nextIndex]->isGivenKind(\T_OBJECT_OPERATOR)) {
                return false;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

            if (null !== $nextNextIndex && $nextNextIndex < $endIndex) {
                return false;
            }

            return null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(\T_STRING);
        }

        if ($tokens[$index]->isGivenKind([\T_NS_SEPARATOR, \T_STRING])) {
            $nextIndex = $tokens->getTokenNotOfKindsSibling($index, 1, [\T_DOUBLE_COLON, \T_NS_SEPARATOR, \T_STRING]);
            $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

            if (null !== $nextNextIndex && $nextNextIndex < $endIndex) {
                return false;
            }

            return null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(\T_VARIABLE);
        }

        return false;
    }

    /**
     * @param list<_UnsetInfo> $unsetsInfo
     */
    private function isAnyUnsetToTransform(array $unsetsInfo): bool
    {
        foreach ($unsetsInfo as $unsetInfo) {
            if ($unsetInfo['isToTransform']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param _UnsetInfo $unsetInfo
     */
    private function updateTokens(Tokens $tokens, array $unsetInfo, bool $isLastUnset): void
    {
        // if entry is first and to be transformed we remove leading "unset("
        if ($unsetInfo['isFirst'] && $unsetInfo['isToTransform']) {
            $braceIndex = $tokens->getPrevTokenOfKind($unsetInfo['startIndex'], ['(']);
            $unsetIndex = $tokens->getPrevTokenOfKind($braceIndex, [[\T_UNSET]]);
            $tokens->clearTokenAndMergeSurroundingWhitespace($braceIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($unsetIndex);
        }

        // if entry is last and to be transformed we remove trailing ")"
        if ($isLastUnset && $unsetInfo['isToTransform']) {
            $braceIndex = $tokens->getNextTokenOfKind($unsetInfo['endIndex'], [')']);
            $previousIndex = $tokens->getPrevMeaningfulToken($braceIndex);
            if ($tokens[$previousIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($previousIndex); // trailing ',' in function call (PHP 7.3)
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($braceIndex);
        }

        // if entry is not last we replace comma with semicolon (last entry already has semicolon - from original unset)
        if (!$isLastUnset) {
            $commaIndex = $tokens->getNextTokenOfKind($unsetInfo['endIndex'], [',']);
            $tokens[$commaIndex] = new Token(';');
        }

        // if entry is to be unset and is not last we add trailing ")"
        if (!$unsetInfo['isToTransform'] && !$isLastUnset) {
            $tokens->insertAt($unsetInfo['endIndex'] + 1, new Token(')'));
        }

        // if entry is to be unset and is not first we add leading "unset("
        if (!$unsetInfo['isToTransform'] && !$unsetInfo['isFirst']) {
            $tokens->insertAt(
                $unsetInfo['startIndex'],
                [
                    new Token([\T_UNSET, 'unset']),
                    new Token('('),
                ],
            );
        }

        // and finally
        // if entry is to be transformed we add trailing " = null"
        if ($unsetInfo['isToTransform']) {
            $tokens->insertAt(
                $unsetInfo['endIndex'] + 1,
                [
                    new Token([\T_WHITESPACE, ' ']),
                    new Token('='),
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_STRING, 'null']),
                ],
            );
        }
    }
}
