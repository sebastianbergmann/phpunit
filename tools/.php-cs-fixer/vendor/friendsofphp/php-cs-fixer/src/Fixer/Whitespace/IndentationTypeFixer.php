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

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶2.4.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class IndentationTypeFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    private string $indent;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Code MUST use configured indentation type.',
            [
                new CodeSample("<?php\n\nif (true) {\n\techo 'Hello!';\n}\n"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocIndentFixer.
     * Must run after ClassAttributesSeparationFixer.
     */
    public function getPriority(): int
    {
        return 50;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT, \T_WHITESPACE]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->indent = $this->whitespacesConfig->getIndent();

        foreach ($tokens as $index => $token) {
            if ($token->isComment()) {
                $tokens[$index] = $this->fixIndentInComment($tokens, $index);

                continue;
            }

            if ($token->isWhitespace()) {
                $tokens[$index] = $this->fixIndentToken($tokens, $index);

                continue;
            }
        }
    }

    private function fixIndentInComment(Tokens $tokens, int $index): Token
    {
        $content = Preg::replace('/^(?:(?<! ) {1,3})?\t/m', '\1    ', $tokens[$index]->getContent(), -1, $count);

        // Also check for more tabs.
        while (0 !== $count) {
            $content = Preg::replace('/^(\ +)?\t/m', '\1    ', $content, -1, $count);
        }

        $indent = $this->indent;

        // change indent to expected one
        $content = Preg::replaceCallback('/^(?:    )+/m', fn (array $matches): string => $this->getExpectedIndent($matches[0], $indent), $content);

        return new Token([$tokens[$index]->getId(), $content]);
    }

    private function fixIndentToken(Tokens $tokens, int $index): Token
    {
        $content = $tokens[$index]->getContent();
        $previousTokenHasTrailingLinebreak = false;

        // @TODO this can be removed when we have a transformer for "T_OPEN_TAG" to "T_OPEN_TAG + T_WHITESPACE"
        if (str_contains($tokens[$index - 1]->getContent(), "\n")) {
            $content = "\n".$content;
            $previousTokenHasTrailingLinebreak = true;
        }

        $indent = $this->indent;
        $newContent = Preg::replaceCallback(
            '/(\R)(\h+)/', // find indent
            function (array $matches) use ($indent): string {
                // normalize mixed indent
                $content = Preg::replace('/(?:(?<! ) {1,3})?\t/', '    ', $matches[2]);

                // change indent to expected one
                return $matches[1].$this->getExpectedIndent($content, $indent);
            },
            $content,
        );

        if ($previousTokenHasTrailingLinebreak) {
            $newContent = substr($newContent, 1);
        }

        return new Token([\T_WHITESPACE, $newContent]);
    }

    /**
     * @return string mixed
     */
    private function getExpectedIndent(string $content, string $indent): string
    {
        if ("\t" === $indent) {
            $content = str_replace('    ', $indent, $content);
        }

        return $content;
    }
}
