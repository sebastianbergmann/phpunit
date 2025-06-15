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
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\TokenRemover;

/**
 * @no-named-arguments
 */
final class NoImportFromGlobalNamespaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no imports from the global namespace.',
            [new CodeSample('<?php
namespace Foo;
use DateTime;
class Bar {
    public function __construct(DateTime $dateTime) {}
}
')],
            '',
        );
    }

    /**
     * Must run before PhpdocAlignFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_USE);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach (\array_reverse((new NamespacesAnalyzer())->getDeclarations($tokens)) as $namespace) {
            $this->fixImports($tokens, $namespace->getScopeStartIndex(), $namespace->getScopeEndIndex(), $namespace->getFullName() === '');
        }
    }

    private function fixImports(Tokens $tokens, int $startIndex, int $endIndex, bool $isInGlobalNamespace): void
    {
        $importedClassesIndices = self::getImportCandidateIndices($tokens, $startIndex, $endIndex);

        if (!$isInGlobalNamespace) {
            for ($index = $endIndex; $index > $startIndex; $index--) {
                if ($tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                    $importedClassesIndices = $this->updateComment($tokens, $importedClassesIndices, $index);
                    continue;
                }

                if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                    continue;
                }

                $importedClassesIndices = $this->updateUsage($tokens, $importedClassesIndices, $index);
            }
        }

        self::clearImports($tokens, $importedClassesIndices);
    }

    /**
     * @return array<string, null|int>
     */
    private static function getImportCandidateIndices(Tokens $tokens, int $startIndex, int $endIndex): array
    {
        $importedClassesIndices = [];

        foreach (\array_keys($tokens->findGivenKind(\T_USE, $startIndex, $endIndex)) as $index) {
            $classNameIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($classNameIndex));

            if ($tokens[$classNameIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $classNameIndex = $tokens->getNextMeaningfulToken($classNameIndex);
                \assert(\is_int($classNameIndex));
            }

            $semicolonIndex = $tokens->getNextMeaningfulToken($classNameIndex);
            \assert(\is_int($semicolonIndex));

            if (!$tokens[$semicolonIndex]->equals(';')) {
                continue;
            }

            $importedClassesIndices[$tokens[$classNameIndex]->getContent()] = $classNameIndex;
        }

        return $importedClassesIndices;
    }

    /**
     * @param array<string, null|int> $importedClassesIndices
     *
     * @return array<string, null|int>
     */
    private function updateComment(Tokens $tokens, array $importedClassesIndices, int $index): array
    {
        $content = $tokens[$index]->getContent();

        foreach ($importedClassesIndices as $importedClassName => $importedClassIndex) {
            $content = Preg::replace(\sprintf('/\\b(?<!\\\\)%s(?!\\\\)\\b/', $importedClassName), '\\' . $importedClassName, $content);
            if ($importedClassIndex !== null && Preg::match(\sprintf('/\\b(?<!\\\\)%s(?=\\\\)\\b/', $importedClassName), $content)) {
                $importedClassesIndices[$importedClassName] = null;
            }
        }

        if ($content !== $tokens[$index]->getContent()) {
            $tokens[$index] = new Token([\T_DOC_COMMENT, $content]);
        }

        return $importedClassesIndices;
    }

    /**
     * @param array<string, null|int> $importedClassesIndices
     *
     * @return array<string, null|int>
     */
    private function updateUsage(Tokens $tokens, array $importedClassesIndices, int $index): array
    {
        if (!\in_array($tokens[$index]->getContent(), \array_keys($importedClassesIndices), true)) {
            return $importedClassesIndices;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($prevIndex));

        if ($tokens[$prevIndex]->isGivenKind([\T_CONST, \T_DOUBLE_COLON, \T_FUNCTION, \T_NS_SEPARATOR, \T_OBJECT_OPERATOR, \T_USE])) {
            return $importedClassesIndices;
        }

        $nextIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($nextIndex));

        if ($tokens[$nextIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $importedClassesIndices[$tokens[$index]->getContent()] = null;

            return $importedClassesIndices;
        }

        $tokens->insertAt($index, new Token([\T_NS_SEPARATOR, '\\']));

        return $importedClassesIndices;
    }

    /**
     * @param array<string, null|int> $importedClassesIndices
     */
    private static function clearImports(Tokens $tokens, array $importedClassesIndices): void
    {
        foreach ($importedClassesIndices as $importedClassIndex) {
            if ($importedClassIndex === null) {
                continue;
            }
            $useIndex = $tokens->getPrevTokenOfKind($importedClassIndex, [[\T_USE]]);
            \assert(\is_int($useIndex));

            $semicolonIndex = $tokens->getNextTokenOfKind($importedClassIndex, [';']);
            \assert(\is_int($semicolonIndex));

            $tokens->clearRange($useIndex, $semicolonIndex);
            TokenRemover::removeWithLinesIfPossible($tokens, $useIndex);
        }
    }
}
