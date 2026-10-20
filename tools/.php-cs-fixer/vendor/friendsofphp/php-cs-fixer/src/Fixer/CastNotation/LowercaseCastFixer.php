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

namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class LowercaseCastFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Cast should be written in lower case.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                            $a = (BOOLEAN) $b;
                            $a = (BOOL) $b;
                            $a = (INTEGER) $b;
                            $a = (INT) $b;
                            $a = (DOUBLE) $b;
                            $a = (FLoaT) $b;
                            $a = (flOAT) $b;
                            $a = (stRING) $b;
                            $a = (ARRAy) $b;
                            $a = (OBJect) $b;
                            $a = (UNset) $b;
                            $a = (Binary) $b;

                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getCastTokenKinds());
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if (!$tokens[$index]->isCast()) {
                continue;
            }

            $tokens[$index] = new Token([$tokens[$index]->getId(), strtolower($tokens[$index]->getContent())]);
        }
    }
}
