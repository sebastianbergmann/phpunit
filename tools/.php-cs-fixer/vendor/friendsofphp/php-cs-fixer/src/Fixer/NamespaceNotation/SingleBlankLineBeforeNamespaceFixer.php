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

namespace PhpCsFixer\Fixer\NamespaceNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @deprecated Use `blank_lines_before_namespace` with config: ['min_line_breaks' => 2, 'max_line_breaks' => 2] (default)
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SingleBlankLineBeforeNamespaceFixer extends AbstractProxyFixer implements WhitespacesAwareFixerInterface, DeprecatedFixerInterface
{
    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should be exactly one blank line before a namespace declaration.',
            [
                new CodeSample("<?php  namespace A {}\n"),
                new CodeSample("<?php\n\n\nnamespace A{}\n"),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_NAMESPACE);
    }

    /**
     * {@inheritdoc}
     *
     * Must run after HeaderCommentFixer.
     */
    public function getPriority(): int
    {
        return parent::getPriority();
    }

    protected function createProxyFixers(): array
    {
        $blankLineBeforeNamespace = new BlankLinesBeforeNamespaceFixer();
        $blankLineBeforeNamespace->configure([
            'min_line_breaks' => 2,
            'max_line_breaks' => 2,
        ]);

        return [
            $blankLineBeforeNamespace,
        ];
    }
}
