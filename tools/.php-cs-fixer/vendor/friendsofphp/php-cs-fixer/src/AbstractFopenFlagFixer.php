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

namespace PhpCsFixer;

use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractFopenFlagFixer extends AbstractFunctionReferenceFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_STRING, \T_CONSTANT_ENCAPSED_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        $index = 0;
        $end = $tokens->count() - 1;
        while (true) {
            $candidate = $this->find('fopen', $tokens, $index, $end);

            if (null === $candidate) {
                break;
            }

            $index = $candidate[1]; // proceed to '(' of `fopen`

            // fetch arguments
            $arguments = $argumentsAnalyzer->getArguments(
                $tokens,
                $index,
                $candidate[2],
            );

            $argumentsCount = \count($arguments); // argument count sanity check

            if ($argumentsCount < 2 || $argumentsCount > 4) {
                continue;
            }

            // get second argument index
            $argumentKeys = array_keys($arguments);
            \assert(isset($argumentKeys[1]));
            $argumentStartIndex = $argumentKeys[1];

            \assert(isset($arguments[$argumentStartIndex]));
            $this->fixFopenFlagToken(
                $tokens,
                $argumentStartIndex,
                $arguments[$argumentStartIndex],
            );
        }
    }

    abstract protected function fixFopenFlagToken(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): void;

    protected function isValidModeString(string $mode): bool
    {
        $modeLength = \strlen($mode);
        if ($modeLength < 1 || $modeLength > 13) { // 13 === length 'r+w+a+x+c+etb'
            return false;
        }

        $validFlags = [
            'a' => true,
            'b' => true,
            'c' => true,
            'e' => true,
            'r' => true,
            't' => true,
            'w' => true,
            'x' => true,
        ];

        if (!isset($validFlags[$mode[0]])) {
            return false;
        }

        unset($validFlags[$mode[0]]);

        for ($i = 1; $i < $modeLength; ++$i) {
            if (isset($validFlags[$mode[$i]])) {
                unset($validFlags[$mode[$i]]);

                continue;
            }

            if ('+' !== $mode[$i]
                || (
                    'a' !== $mode[$i - 1] // 'a+','c+','r+','w+','x+'
                    && 'c' !== $mode[$i - 1]
                    && 'r' !== $mode[$i - 1]
                    && 'w' !== $mode[$i - 1]
                    && 'x' !== $mode[$i - 1]
                )
            ) {
                return false;
            }
        }

        return true;
    }
}
