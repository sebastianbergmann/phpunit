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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶5.2.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SwitchCaseSemicolonToColonFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A case should be followed by a colon and not a semicolon.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                            switch ($a) {
                                case 1;
                                    break;
                                default;
                                    break;
                            }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoEmptyStatementFixer.
     */
    public function getPriority(): int
    {
        return 0;
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
                $this->fixTokenIfNeeded($tokens, $default->getColonIndex());
            }

            foreach ($analysis->getCases() as $caseAnalysis) {
                $this->fixTokenIfNeeded($tokens, $caseAnalysis->getColonIndex());
            }
        }
    }

    private function fixTokenIfNeeded(Tokens $tokens, int $index): void
    {
        if ($tokens[$index]->equals(';')) {
            $tokens[$index] = new Token(':');
        }
    }
}
