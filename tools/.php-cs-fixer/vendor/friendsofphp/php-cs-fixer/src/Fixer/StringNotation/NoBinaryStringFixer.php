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

namespace PhpCsFixer\Fixer\StringNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author ntzm
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoBinaryStringFixer extends AbstractFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(
            [
                \T_CONSTANT_ENCAPSED_STRING,
                \T_START_HEREDOC,
                'b"',
            ],
        );
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be a binary flag before strings.',
            [
                new CodeSample("<?php \$a = b'foo';\n"),
                new CodeSample("<?php \$a = b<<<EOT\nfoo\nEOT;\n"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoUselessConcatOperatorFixer, PhpUnitDedicateAssertInternalTypeFixer, RegularCallableCallFixer, SetTypeToCastFixer.
     */
    public function getPriority(): int
    {
        return 40;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind([\T_CONSTANT_ENCAPSED_STRING, \T_START_HEREDOC])) {
                $content = $token->getContent();

                if ('b' === strtolower($content[0])) {
                    $tokens[$index] = new Token([$token->getId(), substr($content, 1)]);
                }
            } elseif ($token->equals('b"')) {
                $tokens[$index] = new Token('"');
            }
        }
    }
}
