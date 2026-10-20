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

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SwitchAnalyzer
{
    /** @var array<non-empty-string, list<int>> */
    private static array $cache = [];

    public static function belongsToSwitch(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals(':')) {
            return false;
        }

        $collectionHash = $tokens->getCollectionHash();

        if (!\array_key_exists($collectionHash, self::$cache)) {
            self::$cache[$collectionHash] = self::getColonIndicesForSwitch(clone $tokens);
        }

        $arr = self::$cache[$collectionHash];

        return \in_array($index, $arr, true);
    }

    /**
     * @return list<int>
     */
    private static function getColonIndicesForSwitch(Tokens $tokens): array
    {
        $colonIndices = [];

        /** @var SwitchAnalysis $analysis */
        foreach (ControlCaseStructuresAnalyzer::findControlStructures($tokens, [\T_SWITCH]) as $analysis) {
            if ($tokens[$analysis->getOpenIndex()]->equals(':')) {
                $colonIndices[] = $analysis->getOpenIndex();
            }

            foreach ($analysis->getCases() as $case) {
                $colonIndices[] = $case->getColonIndex();
            }

            $defaultAnalysis = $analysis->getDefaultAnalysis();

            if (null !== $defaultAnalysis) {
                $colonIndices[] = $defaultAnalysis->getColonIndex();
            }
        }

        return $colonIndices;
    }
}
