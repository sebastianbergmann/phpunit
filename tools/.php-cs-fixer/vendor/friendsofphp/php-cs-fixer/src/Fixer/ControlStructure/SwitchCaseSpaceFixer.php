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
use PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\ControlCaseStructuresAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶5.2.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SwitchCaseSpaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Removes extra spaces between colon and case value.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                            switch($a) {
                                case 1   :
                                    break;
                                default     :
                                    return 2;
                            }

                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_SWITCH);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        /** @var SwitchAnalysis $analysis */
        foreach (ControlCaseStructuresAnalyzer::findControlStructures($tokens, [\T_SWITCH]) as $analysis) {
            $default = $analysis->getDefaultAnalysis();

            if (null !== $default) {
                $index = $default->getIndex();

                if (!$tokens[$index + 1]->isWhitespace() || !$tokens[$index + 2]->equalsAny([':', ';'])) {
                    continue;
                }

                $tokens->clearAt($index + 1);
            }

            foreach ($analysis->getCases() as $caseAnalysis) {
                $colonIndex = $caseAnalysis->getColonIndex();
                $valueIndex = $tokens->getPrevNonWhitespace($colonIndex);

                // skip if there is no space between the colon and previous token or is space after comment
                if ($valueIndex === $colonIndex - 1 || $tokens[$valueIndex]->isComment()) {
                    continue;
                }

                $tokens->clearAt($valueIndex + 1);
            }
        }
    }
}
