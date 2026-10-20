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
 * @deprecated Use `blank_lines_before_namespace` with config: ['min_line_breaks' => 0, 'max_line_breaks' => 1]
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoBlankLinesBeforeNamespaceFixer extends AbstractProxyFixer implements WhitespacesAwareFixerInterface, DeprecatedFixerInterface
{
    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_NAMESPACE);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should be no blank lines before a namespace declaration.',
            [
                new CodeSample(
                    "<?php\n\n\n\nnamespace Example;\n",
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after BlankLineAfterOpeningTagFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    protected function createProxyFixers(): array
    {
        $blankLineBeforeNamespace = new BlankLinesBeforeNamespaceFixer();
        $blankLineBeforeNamespace->configure([
            'min_line_breaks' => 0,
            'max_line_breaks' => 1,
        ]);

        return [
            $blankLineBeforeNamespace,
        ];
    }
}
