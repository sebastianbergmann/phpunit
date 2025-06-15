<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class StringableInterfaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A class that implements the `__toString()` method must explicitly implement the `Stringable` interface.',
            [new CodeSample('<?php
class Foo
{
   public function __toString()
   {
        return "Foo";
   }
}
')],
            '',
        );
    }

    /**
     * Must run before ClassDefinitionFixer, GlobalNamespaceImportFixer, OrderedInterfacesFixer.
     */
    public function getPriority(): int
    {
        return 37;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        // @phpstan-ignore greaterOrEqual.alwaysTrue
        return \PHP_VERSION_ID >= 80000 && $tokens->isAllTokenKindsFound([\T_CLASS, \T_STRING]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $namespaceStartIndex = 0;

        for ($index = 1; $index < $tokens->count(); $index++) {
            if ($tokens[$index]->isGivenKind(\T_NAMESPACE)) {
                $namespaceStartIndex = $index;
                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $classStartIndex = $tokens->getNextTokenOfKind($index, ['{']);
            \assert(\is_int($classStartIndex));

            $classEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classStartIndex);

            if (!$this->doesHaveToStringMethod($tokens, $classStartIndex, $classEndIndex)) {
                continue;
            }

            if ($this->doesImplementStringable($tokens, $namespaceStartIndex, $index, $classStartIndex)) {
                continue;
            }

            $this->addStringableInterface($tokens, $index);
        }
    }

    private function doesHaveToStringMethod(Tokens $tokens, int $classStartIndex, int $classEndIndex): bool
    {
        $index = $classStartIndex;

        while ($index < $classEndIndex) {
            $index++;

            if ($tokens[$index]->equals('{')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $functionNameIndex = $tokens->getNextMeaningfulToken($index);

            if ($tokens[$functionNameIndex]->equals([\T_STRING, '__toString'], false)) {
                return true;
            }
        }

        return false;
    }

    private function doesImplementStringable(Tokens $tokens, int $namespaceStartIndex, int $classKeywordIndex, int $classOpenBraceIndex): bool
    {
        $interfaces = $this->getInterfaces($tokens, $classKeywordIndex, $classOpenBraceIndex);
        if ($interfaces === []) {
            return false;
        }

        if (\in_array('\\stringable', $interfaces, true)) {
            return true;
        }

        if ($namespaceStartIndex === 0 && \in_array('stringable', $interfaces, true)) {
            return true;
        }

        foreach ($this->getImports($tokens, $namespaceStartIndex, $classKeywordIndex) as $import) {
            if (\in_array($import, $interfaces, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function getInterfaces(Tokens $tokens, int $classKeywordIndex, int $classOpenBraceIndex): array
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
                $interfaces[] = \strtolower($interface);
                $interface = '';
                continue;
            }
            $interface .= $tokens[$index]->getContent();
        }
        if ($interface !== '') {
            $interfaces[] = \strtolower($interface);
        }

        return $interfaces;
    }

    /**
     * @return iterable<string>
     */
    private function getImports(Tokens $tokens, int $namespaceStartIndex, int $classKeywordIndex): iterable
    {
        for ($index = $namespaceStartIndex; $index < $classKeywordIndex; $index++) {
            if (!$tokens[$index]->isGivenKind(\T_USE)) {
                continue;
            }
            $nameIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($nameIndex));

            if ($tokens[$nameIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $nameIndex = $tokens->getNextMeaningfulToken($nameIndex);
                \assert(\is_int($nameIndex));
            }

            $nextIndex = $tokens->getNextMeaningfulToken($nameIndex);
            \assert(\is_int($nextIndex));
            if ($tokens[$nextIndex]->isGivenKind(\T_AS)) {
                $nameIndex = $tokens->getNextMeaningfulToken($nextIndex);
                \assert(\is_int($nameIndex));
            }

            yield \strtolower($tokens[$nameIndex]->getContent());
        }
    }

    private function addStringableInterface(Tokens $tokens, int $classIndex): void
    {
        $implementsIndex = $tokens->getNextTokenOfKind($classIndex, ['{', [\T_IMPLEMENTS]]);
        \assert(\is_int($implementsIndex));

        if ($tokens[$implementsIndex]->equals('{')) {
            $prevIndex = $tokens->getPrevMeaningfulToken($implementsIndex);
            \assert(\is_int($prevIndex));

            $tokens->insertAt(
                $prevIndex + 1,
                [
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_IMPLEMENTS, 'implements']),
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_NS_SEPARATOR, '\\']),
                    new Token([\T_STRING, \Stringable::class]),
                ],
            );

            return;
        }

        $afterImplementsIndex = $tokens->getNextMeaningfulToken($implementsIndex);
        \assert(\is_int($afterImplementsIndex));

        $tokens->insertAt(
            $afterImplementsIndex,
            [
                new Token([\T_NS_SEPARATOR, '\\']),
                new Token([\T_STRING, \Stringable::class]),
                new Token(','),
                new Token([\T_WHITESPACE, ' ']),
            ],
        );
    }
}
