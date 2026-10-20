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

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DateTimeCreateFromFormatCallFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The first argument of `DateTime::createFromFormat` method must start with `!`.',
            [
                new CodeSample("<?php \\DateTime::createFromFormat('Y-m-d', '2022-02-11');\n"),
            ],
            "Consider this code:
    `DateTime::createFromFormat('Y-m-d', '2022-02-11')`.
    What value will be returned? '2022-02-11 00:00:00.0'?
    No, actual return value has 'H:i:s' section like '2022-02-11 16:55:37.0'.
    Change 'Y-m-d' to '!Y-m-d', return value will be '2022-02-11 00:00:00.0'.
    So, adding `!` to format string will make return value more intuitive.",
            'Risky when depending on the actual timings being used even when not explicit set in format.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoUselessConcatOperatorFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOUBLE_COLON);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();
        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();

        foreach ($tokens->getNamespaceDeclarations() as $namespace) {
            $scopeStartIndex = $namespace->getScopeStartIndex();
            $useDeclarations = $namespaceUsesAnalyzer->getDeclarationsInNamespace($tokens, $namespace);

            for ($index = $namespace->getScopeEndIndex(); $index > $scopeStartIndex; --$index) {
                if (!$tokens[$index]->isGivenKind(\T_DOUBLE_COLON)) {
                    continue;
                }

                $functionNameIndex = $tokens->getNextMeaningfulToken($index);

                if (!$tokens[$functionNameIndex]->equals([\T_STRING, 'createFromFormat'], false)) {
                    continue;
                }

                if (!$tokens[$tokens->getNextMeaningfulToken($functionNameIndex)]->equals('(')) {
                    continue;
                }

                $classNameIndex = $tokens->getPrevMeaningfulToken($index);

                if (!$tokens[$classNameIndex]->equalsAny([[\T_STRING, \DateTime::class], [\T_STRING, \DateTimeImmutable::class]], false)) {
                    continue;
                }

                $preClassNameIndex = $tokens->getPrevMeaningfulToken($classNameIndex);

                if ($tokens[$preClassNameIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                    if ($tokens[$tokens->getPrevMeaningfulToken($preClassNameIndex)]->isGivenKind(\T_STRING)) {
                        continue;
                    }
                } elseif (!$namespace->isGlobalNamespace()) {
                    continue;
                } else {
                    foreach ($useDeclarations as $useDeclaration) {
                        foreach (['datetime', 'datetimeimmutable'] as $name) {
                            if ($name === strtolower($useDeclaration->getShortName()) && $name !== strtolower($useDeclaration->getFullName())) {
                                continue 3;
                            }
                        }
                    }
                }

                $openIndex = $tokens->getNextTokenOfKind($functionNameIndex, ['(']);
                $closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openIndex);

                $argumentIndex = $this->getFirstArgumentTokenIndex($tokens, $argumentsAnalyzer->getArguments($tokens, $openIndex, $closeIndex));

                if (null === $argumentIndex) {
                    continue;
                }

                $format = $tokens[$argumentIndex]->getContent();

                if (\strlen($format) < 3) {
                    continue;
                }

                $offset = 'b' === $format[0] || 'B' === $format[0] ? 2 : 1;

                if ('!' === $format[$offset]) {
                    continue;
                }

                $tokens->clearAt($argumentIndex);
                $tokens->insertAt($argumentIndex, new Token([\T_CONSTANT_ENCAPSED_STRING, substr_replace($format, '!', $offset, 0)]));
            }
        }
    }

    /**
     * @param array<int, int> $arguments
     */
    private function getFirstArgumentTokenIndex(Tokens $tokens, array $arguments): ?int
    {
        if (2 !== \count($arguments)) {
            return null;
        }

        $argumentStartIndex = array_key_first($arguments);
        $argumentEndIndex = $arguments[$argumentStartIndex];
        $argumentStartIndex = $tokens->getNextMeaningfulToken($argumentStartIndex - 1);

        if (
            $argumentStartIndex !== $argumentEndIndex
            && $tokens->getNextMeaningfulToken($argumentStartIndex) <= $argumentEndIndex
        ) {
            return null; // argument is not a simple single string
        }

        return !$tokens[$argumentStartIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)
            ? null // first argument is not a string
            : $argumentStartIndex;
    }
}
