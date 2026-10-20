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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Gregor Harlan
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CombineNestedDirnameFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Replace multiple nested calls of `dirname` by only one call with second `$level` parameter.',
            [
                new CodeSample(
                    "<?php\ndirname(dirname(dirname(\$path)));\n",
                ),
            ],
            null,
            'Risky when the function `dirname` is overridden.',
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Must run before MethodArgumentSpaceFixer, NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     * Must run after DirConstantFixer.
     */
    public function getPriority(): int
    {
        return 35;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            $dirnameInfo = $this->getDirnameInfo($tokens, $index);

            if (false === $dirnameInfo) {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($dirnameInfo['indices'][0]);

            if (!$tokens[$prev]->equals('(')) {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($prev);
            $firstArgumentEnd = $dirnameInfo['end'];
            $dirnameInfoArray = [$dirnameInfo];

            while (($dirnameInfo = $this->getDirnameInfo($tokens, $prev, $firstArgumentEnd)) !== false) {
                $dirnameInfoArray[] = $dirnameInfo;
                $prev = $tokens->getPrevMeaningfulToken($dirnameInfo['indices'][0]);

                if (!$tokens[$prev]->equals('(')) {
                    break;
                }

                $prev = $tokens->getPrevMeaningfulToken($prev);
                $firstArgumentEnd = $dirnameInfo['end'];
            }

            if (\count($dirnameInfoArray) > 1) {
                $this->combineDirnames($tokens, $dirnameInfoArray);
            }

            $index = $prev;
        }
    }

    /**
     * @param int      $index                 Index of `dirname`
     * @param null|int $firstArgumentEndIndex Index of last token of first argument of `dirname` call
     *
     * @return array{indices: non-empty-list<int>, secondArgument?: int, levels: int, end: int}|false `false` when it is not a (supported) `dirname` call, an array with info about the dirname call otherwise
     */
    private function getDirnameInfo(Tokens $tokens, int $index, ?int $firstArgumentEndIndex = null)
    {
        if (!$tokens[$index]->equals([\T_STRING, 'dirname'], false)) {
            return false;
        }

        if (!(new FunctionsAnalyzer())->isGlobalFunctionCall($tokens, $index)) {
            return false;
        }

        $info = ['indices' => []];
        $prev = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$prev]->isGivenKind(\T_NS_SEPARATOR)) {
            $info['indices'][] = $prev;
        }

        $info['indices'][] = $index;

        // opening parenthesis "("
        $next = $tokens->getNextMeaningfulToken($index);
        $info['indices'][] = $next;

        if (null !== $firstArgumentEndIndex) {
            $next = $tokens->getNextMeaningfulToken($firstArgumentEndIndex);
        } else {
            $next = $tokens->getNextMeaningfulToken($next);

            if ($tokens[$next]->equals(')')) {
                return false;
            }

            while (!$tokens[$next]->equalsAny([',', ')'])) {
                $blockType = Tokens::detectBlockType($tokens[$next]);

                if (null !== $blockType) {
                    $next = $tokens->findBlockEnd($blockType['type'], $next);
                }

                $next = $tokens->getNextMeaningfulToken($next);
            }
        }

        $info['indices'][] = $next;

        if ($tokens[$next]->equals(',')) {
            $next = $tokens->getNextMeaningfulToken($next);
            $info['indices'][] = $next;
        }

        if ($tokens[$next]->equals(')')) {
            $info['levels'] = 1;
            $info['end'] = $next;

            return $info;
        }

        if (!$tokens[$next]->isGivenKind(\T_LNUMBER)) {
            return false;
        }

        $info['secondArgument'] = $next;
        $info['levels'] = (int) $tokens[$next]->getContent();

        $next = $tokens->getNextMeaningfulToken($next);

        if ($tokens[$next]->equals(',')) {
            $info['indices'][] = $next;
            $next = $tokens->getNextMeaningfulToken($next);
        }

        if (!$tokens[$next]->equals(')')) {
            return false;
        }

        $info['indices'][] = $next;
        $info['end'] = $next;

        return $info;
    }

    /**
     * @param non-empty-list<array{indices: non-empty-list<int>, secondArgument?: int, levels: int, end: int}> $dirnameInfoArray
     */
    private function combineDirnames(Tokens $tokens, array $dirnameInfoArray): void
    {
        $outerDirnameInfo = array_pop($dirnameInfoArray);
        $levels = $outerDirnameInfo['levels'];

        foreach ($dirnameInfoArray as $dirnameInfo) {
            $levels += $dirnameInfo['levels'];

            foreach ($dirnameInfo['indices'] as $index) {
                $tokens->removeLeadingWhitespace($index);
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            }
        }

        $levelsToken = new Token([\T_LNUMBER, (string) $levels]);

        if (isset($outerDirnameInfo['secondArgument'])) {
            $tokens[$outerDirnameInfo['secondArgument']] = $levelsToken;
        } else {
            $prev = $tokens->getPrevMeaningfulToken($outerDirnameInfo['end']);
            $items = [];

            if (!$tokens[$prev]->equals(',')) {
                $items = [new Token(','), new Token([\T_WHITESPACE, ' '])];
            }

            $items[] = $levelsToken;
            $tokens->insertAt($outerDirnameInfo['end'], $items);
        }
    }
}
