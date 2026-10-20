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

namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Santiago San Martin <sanmartindev@gmail.com>
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class StringableForToStringFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A class that implements the `__toString()` method must explicitly implement the `Stringable` interface.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo
                        {
                            public function __toString()
                            {
                                return "Foo";
                            }
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before ClassDefinitionFixer, GlobalNamespaceImportFixer, OrderedInterfacesFixer.
     */
    public function getPriority(): int
    {
        return 37;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \PHP_VERSION_ID >= 8_00_00 && $tokens->isAllTokenKindsFound([\T_CLASS, \T_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $useDeclarations = (new NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);

        $stringableInterfaces = ['stringable'];

        for ($index = 1; $index < $tokens->count(); ++$index) {
            if ($tokens[$index]->isGivenKind(\T_NAMESPACE)) {
                $stringableInterfaces = [];

                continue;
            }

            if ($tokens[$index]->isGivenKind(\T_USE)) {
                $name = self::getNameFromUse($index, $useDeclarations);
                if (null !== $name) {
                    $stringableInterfaces[] = $name;
                }

                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $classStartIndex = $tokens->getNextTokenOfKind($index, ['{']);
            \assert(\is_int($classStartIndex));

            $classEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $classStartIndex);

            if (!self::doesHaveToStringMethod($tokens, $classStartIndex, $classEndIndex)) {
                continue;
            }

            if (self::doesImplementStringable($tokens, $index, $classStartIndex, $stringableInterfaces)) {
                continue;
            }

            self::addStringableInterface($tokens, $index);
        }
    }

    /**
     * @param list<NamespaceUseAnalysis> $useDeclarations
     */
    private static function getNameFromUse(int $index, array $useDeclarations): ?string
    {
        $uses = array_filter(
            $useDeclarations,
            static fn (NamespaceUseAnalysis $namespaceUseAnalysis): bool => $namespaceUseAnalysis->getStartIndex() === $index,
        );

        if (0 === \count($uses)) {
            return null;
        }

        \assert(1 === \count($uses));

        $useDeclaration = reset($uses);

        $lowercasedFullName = strtolower($useDeclaration->getFullName());
        if ('stringable' !== $lowercasedFullName && '\stringable' !== $lowercasedFullName) {
            return null;
        }

        return strtolower($useDeclaration->getShortName());
    }

    private static function doesHaveToStringMethod(Tokens $tokens, int $classStartIndex, int $classEndIndex): bool
    {
        $index = $classStartIndex;

        while ($index < $classEndIndex) {
            ++$index;

            if ($tokens[$index]->equals('{')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($functionNameIndex));

            if ($tokens[$functionNameIndex]->equals([\T_STRING, '__toString'], false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $stringableInterfaces
     */
    private static function doesImplementStringable(
        Tokens $tokens,
        int $classKeywordIndex,
        int $classOpenBraceIndex,
        array $stringableInterfaces
    ): bool {
        $implementedInterfaces = self::getInterfaces($tokens, $classKeywordIndex, $classOpenBraceIndex);
        if ([] === $implementedInterfaces) {
            return false;
        }
        if (\in_array('\stringable', $implementedInterfaces, true)) {
            return true;
        }

        foreach ($stringableInterfaces as $stringableInterface) {
            if (\in_array($stringableInterface, $implementedInterfaces, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function getInterfaces(Tokens $tokens, int $classKeywordIndex, int $classOpenBraceIndex): array
    {
        $implementsIndex = $tokens->getNextTokenOfKind($classKeywordIndex, ['{', [\T_IMPLEMENTS]]);
        \assert(\is_int($implementsIndex));

        $interfaces = [];
        $interface = '';
        for (
            $index = $tokens->getNextMeaningfulToken($implementsIndex);
            $index < $classOpenBraceIndex;
            $index = $tokens->getNextMeaningfulToken($index)
        ) {
            \assert(\is_int($index));
            if ($tokens[$index]->equals(',')) {
                $interfaces[] = strtolower($interface);
                $interface = '';

                continue;
            }
            $interface .= $tokens[$index]->getContent();
        }
        if ('' !== $interface) {
            $interfaces[] = strtolower($interface);
        }

        return $interfaces;
    }

    private static function addStringableInterface(Tokens $tokens, int $classIndex): void
    {
        $implementsIndex = $tokens->getNextTokenOfKind($classIndex, ['{', [\T_IMPLEMENTS]]);
        \assert(\is_int($implementsIndex));

        if ($tokens[$implementsIndex]->equals('{')) {
            $prevIndex = $tokens->getPrevMeaningfulToken($implementsIndex);
            \assert(\is_int($prevIndex));

            $tokens->insertSlices([
                $prevIndex + 1 => [
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_IMPLEMENTS, 'implements']),
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_NS_SEPARATOR, '\\']),
                    new Token([\T_STRING, \Stringable::class]),
                ],
            ]);

            return;
        }

        $afterImplementsIndex = $tokens->getNextMeaningfulToken($implementsIndex);
        \assert(\is_int($afterImplementsIndex));

        $tokens->insertSlices([
            $afterImplementsIndex => [
                new Token([\T_NS_SEPARATOR, '\\']),
                new Token([\T_STRING, \Stringable::class]),
                new Token(','),
                new Token([\T_WHITESPACE, ' ']),
            ],
        ]);
    }
}
