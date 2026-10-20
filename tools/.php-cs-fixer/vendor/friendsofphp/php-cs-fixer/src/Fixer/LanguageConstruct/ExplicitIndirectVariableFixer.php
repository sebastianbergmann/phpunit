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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ExplicitIndirectVariableFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Add curly braces to indirect variables to make them clear to understand.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php
                        echo $$foo;
                        echo $$foo['bar'];
                        echo $foo->$bar['baz'];
                        echo $foo->$callback($baz);

                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_VARIABLE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 1; --$index) {
            $token = $tokens[$index];
            if (!$token->isGivenKind(\T_VARIABLE)) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$prevIndex];
            if (!$prevToken->equals('$') && !$prevToken->isObjectOperator()) {
                continue;
            }

            $openingBrace = CT::T_DYNAMIC_VAR_BRACE_OPEN;
            $closingBrace = CT::T_DYNAMIC_VAR_BRACE_CLOSE;
            if ($prevToken->isObjectOperator()) {
                $openingBrace = CT::T_DYNAMIC_PROP_BRACE_OPEN;
                $closingBrace = CT::T_DYNAMIC_PROP_BRACE_CLOSE;
            }

            $tokens->overrideRange($index, $index, [
                new Token([$openingBrace, '{']),
                new Token([\T_VARIABLE, $token->getContent()]),
                new Token([$closingBrace, '}']),
            ]);
        }
    }
}
