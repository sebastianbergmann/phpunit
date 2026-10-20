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

use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class GotoLabelAnalyzer
{
    public function belongsToGoToLabel(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals(':')) {
            return false;
        }

        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);

        if (!$tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_STRING)) {
            return false;
        }

        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($prevMeaningfulTokenIndex);

        return $tokens[$prevMeaningfulTokenIndex]->equalsAny([':', ';', '{', '}', [\T_OPEN_TAG]]);
    }
}
