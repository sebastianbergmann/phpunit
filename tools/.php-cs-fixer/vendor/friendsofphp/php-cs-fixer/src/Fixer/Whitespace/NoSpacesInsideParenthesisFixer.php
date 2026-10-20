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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶4.3, ¶4.6, ¶5.
 *
 * @author Marc Aubé
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @deprecated in favour of SpacesInsideParenthesisFixer
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoSpacesInsideParenthesisFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There MUST NOT be a space after the opening parenthesis. There MUST NOT be a space before the closing parenthesis.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        if ( $a ) {
                            foo( );
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        function foo( $bar, $baz )
                        {
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before FunctionToConstantFixer, GetClassToClassKeywordFixer, StringLengthToEmptyFixer.
     * Must run after CombineConsecutiveIssetsFixer, CombineNestedDirnameFixer, IncrementStyleFixer, LambdaNotUsedImportFixer, ModernizeStrposFixer, NoUselessSprintfFixer, PowToExponentiationFixer.
     */
    public function getPriority(): int
    {
        return 3;
    }

    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('(');
    }

    protected function createProxyFixers(): array
    {
        return [new SpacesInsideParenthesesFixer()];
    }
}
