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

namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Carlos Cirello <carlos.cirello.nl@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoLeadingImportSlashFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove leading slashes in `use` clauses.',
            [new CodeSample("<?php\nnamespace Foo;\nuse \\Bar;\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before OrderedImportsFixer.
     * Must run after NoUnusedImportsFixer, SingleImportPerStatementFixer.
     */
    public function getPriority(): int
    {
        return -20;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_USE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $usesIndices = $tokensAnalyzer->getImportUseIndexes();

        foreach ($usesIndices as $idx) {
            $nextTokenIdx = $tokens->getNextMeaningfulToken($idx);
            $nextToken = $tokens[$nextTokenIdx];

            if ($nextToken->isGivenKind(\T_NS_SEPARATOR)) {
                $this->removeLeadingImportSlash($tokens, $nextTokenIdx);
            } elseif ($nextToken->isGivenKind([CT::T_FUNCTION_IMPORT, CT::T_CONST_IMPORT])) {
                $nextTokenIdx = $tokens->getNextMeaningfulToken($nextTokenIdx);
                if ($tokens[$nextTokenIdx]->isGivenKind(\T_NS_SEPARATOR)) {
                    $this->removeLeadingImportSlash($tokens, $nextTokenIdx);
                }
            }
        }
    }

    private function removeLeadingImportSlash(Tokens $tokens, int $index): void
    {
        $previousIndex = $tokens->getPrevNonWhitespace($index);

        if (
            $previousIndex < $index - 1
            || $tokens[$previousIndex]->isComment()
        ) {
            $tokens->clearAt($index);

            return;
        }

        $tokens[$index] = new Token([\T_WHITESPACE, ' ']);
    }
}
