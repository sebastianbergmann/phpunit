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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocListTypeFixer extends AbstractPhpdocTypesFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPDoc `list` type must be used instead of `array` without a key.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * @param array<int> $x
                         * @param array<array<string>> $y
                         */

                        PHP,
                ),
            ],
            null,
            'Risky when `array` key should be present, but is missing.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer, PhpdocNoDuplicateTypesFixer, PhpdocTypesOrderFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocArrayTypeFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    protected function normalize(string $type): string
    {
        return Preg::replace('/\barray(?=<(?:[^,<]|<[^>]+>)+(>|{|\())/i', 'list', $type);
    }
}
