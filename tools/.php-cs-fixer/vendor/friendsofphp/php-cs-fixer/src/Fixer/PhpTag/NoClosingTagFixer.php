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

namespace PhpCsFixer\Fixer\PhpTag;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶2.2.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoClosingTagFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The closing `?>` tag MUST be omitted from files containing only PHP.',
            [new CodeSample("<?php\nclass Sample\n{\n}\n?>\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \count($tokens) >= 2 && $tokens->isMonolithicPhp() && $tokens->isTokenKindFound(\T_CLOSE_TAG);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $closeTags = $tokens->findGivenKind(\T_CLOSE_TAG);
        $index = array_key_first($closeTags);

        if (isset($tokens[$index - 1]) && $tokens[$index - 1]->isWhitespace()) {
            $tokens->clearAt($index - 1);
        }
        $tokens->clearAt($index);

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$prevIndex]->equalsAny([';', '}', [\T_OPEN_TAG]])) {
            $tokens->insertAt($prevIndex + 1, new Token(';'));
        }
    }
}
