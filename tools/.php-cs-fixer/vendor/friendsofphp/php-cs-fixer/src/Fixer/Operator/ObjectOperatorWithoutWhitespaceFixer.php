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

namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ObjectOperatorWithoutWhitespaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be space before or after object operators `->` and `?->`.',
            [new CodeSample("<?php \$a  ->  b;\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getObjectOperatorKinds());
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        // [Structure] there should not be space before or after "->" or "?->"
        foreach ($tokens as $index => $token) {
            if (!$token->isObjectOperator()) {
                continue;
            }

            // clear whitespace before ->
            if ($tokens[$index - 1]->isWhitespace(" \t") && !$tokens[$index - 2]->isComment()) {
                $tokens->clearAt($index - 1);
            }

            // clear whitespace after ->
            if ($tokens[$index + 1]->isWhitespace(" \t") && !$tokens[$index + 2]->isComment()) {
                $tokens->clearAt($index + 1);
            }
        }
    }
}
