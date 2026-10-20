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

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @deprecated in favour of PhpdocNoDuplicateTypesFixer
 */
final class PhpdocTypesNoDuplicatesFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Removes duplicate PHPDoc types.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * @param string|string|int $bar
                         */

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return $this->createProxyFixers()[0]->getPriority();
    }

    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    protected function createProxyFixers(): array
    {
        $fixer = new PhpdocNoDuplicateTypesFixer();

        return [$fixer];
    }
}
