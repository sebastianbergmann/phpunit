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
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Sander Verkuil <s.verkuil@pm.me>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class BlankLineBetweenImportGroupsFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    private const IMPORT_TYPE_CLASS = 'class';

    private const IMPORT_TYPE_CONST = 'const';

    private const IMPORT_TYPE_FUNCTION = 'function';

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Putting blank lines between `use` statement groups.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php

                        use function AAC;
                        use const AAB;
                        use AAA;

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        use const AAAA;
                        use const BBB;
                        use Bar;
                        use AAC;
                        use Acme;
                        use function CCC\AA;
                        use function DDD;

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        use const BBB;
                        use const AAAA;
                        use Acme;
                        use AAC;
                        use Bar;
                        use function DDD;
                        use function CCC\AA;

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        use const AAAA;
                        use const BBB;
                        use Acme;
                        use function DDD;
                        use AAC;
                        use function CCC\AA;
                        use Bar;

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after OrderedImportsFixer.
     */
    public function getPriority(): int
    {
        return -40;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_USE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $namespacesImports = $tokensAnalyzer->getImportUseIndexes(true);

        foreach (array_reverse($namespacesImports) as $uses) {
            $this->walkOverUses($tokens, $uses);
        }
    }

    /**
     * @param list<int> $uses
     */
    private function walkOverUses(Tokens $tokens, array $uses): void
    {
        $usesCount = \count($uses);

        if ($usesCount < 2) {
            return; // nothing to fix
        }

        $previousType = null;

        for ($i = $usesCount - 1; $i >= 0; --$i) {
            \assert(isset($uses[$i]));
            $index = $uses[$i];
            $startIndex = $tokens->getNextMeaningfulToken($index + 1);
            $endIndex = $tokens->getNextTokenOfKind($startIndex, [';', [\T_CLOSE_TAG]]);

            if ($tokens[$startIndex]->isGivenKind(CT::T_CONST_IMPORT)) {
                $type = self::IMPORT_TYPE_CONST;
            } elseif ($tokens[$startIndex]->isGivenKind(CT::T_FUNCTION_IMPORT)) {
                $type = self::IMPORT_TYPE_FUNCTION;
            } else {
                $type = self::IMPORT_TYPE_CLASS;
            }

            if (null !== $previousType && $type !== $previousType) {
                $this->ensureLine($tokens, $endIndex + 1);
            }

            $previousType = $type;
        }
    }

    private function ensureLine(Tokens $tokens, int $index): void
    {
        static $lineEnding;

        if (null === $lineEnding) {
            $lineEnding = $this->whitespacesConfig->getLineEnding();
            $lineEnding .= $lineEnding;
        }

        $index = $this->getInsertIndex($tokens, $index);
        $indent = WhitespacesAnalyzer::detectIndent($tokens, $index);

        $tokens->ensureWhitespaceAtIndex($index, 1, $lineEnding.$indent);
    }

    private function getInsertIndex(Tokens $tokens, int $index): int
    {
        $tokensCount = \count($tokens);

        for (; $index < $tokensCount - 1; ++$index) {
            if (!$tokens[$index]->isWhitespace() && !$tokens[$index]->isComment()) {
                return $index - 1;
            }

            $content = $tokens[$index]->getContent();

            if (str_contains($content, "\n")) {
                break;
            }
        }

        return $index;
    }
}
