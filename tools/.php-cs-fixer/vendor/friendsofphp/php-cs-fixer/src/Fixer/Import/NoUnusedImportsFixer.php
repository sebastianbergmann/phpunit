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

namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\GotoLabelAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUnusedImportsFixer extends AbstractFixer
{
    private const TOKENS_NOT_BEFORE_FUNCTION_CALL = [\T_NEW, FCT::T_ATTRIBUTE];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Unused `use` statements must be removed.',
            [new CodeSample("<?php\nuse \\DateTime;\nuse \\Exception;\n\nnew DateTime();\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BlankLineAfterNamespaceFixer, NoExtraBlankLinesFixer, NoLeadingImportSlashFixer, SingleLineAfterImportsFixer.
     * Must run after ClassKeywordRemoveFixer, GlobalNamespaceImportFixer, PhpUnitDedicateAssertFixer, PhpUnitFqcnAnnotationFixer.
     */
    public function getPriority(): int
    {
        return -10;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_USE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $useDeclarations = (new NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens, true);

        if (0 === \count($useDeclarations)) {
            return;
        }

        foreach ($tokens->getNamespaceDeclarations() as $namespace) {
            $currentNamespaceUseDeclarations = [];
            $currentNamespaceUseDeclarationIndices = [];

            foreach ($useDeclarations as $useDeclaration) {
                if ($useDeclaration->getStartIndex() >= $namespace->getScopeStartIndex() && $useDeclaration->getEndIndex() <= $namespace->getScopeEndIndex()) {
                    $currentNamespaceUseDeclarations[] = $useDeclaration;
                    $currentNamespaceUseDeclarationIndices[$useDeclaration->getStartIndex()] = $useDeclaration->getEndIndex();
                }
            }

            foreach ($currentNamespaceUseDeclarations as $useDeclaration) {
                if (!$this->isImportUsed($tokens, $namespace, $useDeclaration, $currentNamespaceUseDeclarationIndices)) {
                    $this->removeUseDeclaration($tokens, $useDeclaration);
                }
            }

            $this->removeUsesInSameNamespace($tokens, $currentNamespaceUseDeclarations, $namespace);
        }
    }

    /**
     * @param array<int, int> $ignoredIndices indices of the use statements themselves that should not be checked as being "used"
     */
    private function isImportUsed(Tokens $tokens, NamespaceAnalysis $namespace, NamespaceUseAnalysis $import, array $ignoredIndices): bool
    {
        $analyzer = new TokensAnalyzer($tokens);
        $gotoLabelAnalyzer = new GotoLabelAnalyzer();

        $namespaceEndIndex = $namespace->getScopeEndIndex();
        $inAttribute = false;

        for ($index = $namespace->getScopeStartIndex(); $index <= $namespaceEndIndex; ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(FCT::T_ATTRIBUTE)) {
                $inAttribute = true;

                continue;
            }

            if ($token->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
                $inAttribute = false;

                continue;
            }

            if (isset($ignoredIndices[$index])) {
                $index = $ignoredIndices[$index];

                continue;
            }

            if ($token->isGivenKind(\T_STRING)) {
                if (0 !== strcasecmp($import->getShortName(), $token->getContent())) {
                    continue;
                }

                $prevMeaningfulToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

                if ($prevMeaningfulToken->isGivenKind(\T_NAMESPACE)) {
                    $index = $tokens->getNextTokenOfKind($index, [';', '{', [\T_CLOSE_TAG]]);

                    continue;
                }

                if (
                    $prevMeaningfulToken->isGivenKind([\T_NS_SEPARATOR, \T_FUNCTION, \T_DOUBLE_COLON])
                    || $prevMeaningfulToken->isObjectOperator()
                ) {
                    continue;
                }

                if ($prevMeaningfulToken->isGivenKind(\T_CONST) && $tokens[$tokens->getNextMeaningfulToken($index)]->equals('=')) {
                    continue;
                }

                if ($inAttribute) {
                    return true;
                }

                $nextMeaningfulIndex = $tokens->getNextMeaningfulToken($index);

                if ($gotoLabelAnalyzer->belongsToGoToLabel($tokens, $nextMeaningfulIndex)) {
                    continue;
                }

                $nextMeaningfulToken = $tokens[$nextMeaningfulIndex];

                if ($analyzer->isConstantInvocation($index)) {
                    $type = NamespaceUseAnalysis::TYPE_CONSTANT;
                } elseif ($nextMeaningfulToken->equals('(') && !$prevMeaningfulToken->isGivenKind(self::TOKENS_NOT_BEFORE_FUNCTION_CALL)) {
                    $type = NamespaceUseAnalysis::TYPE_FUNCTION;
                } else {
                    $type = NamespaceUseAnalysis::TYPE_CLASS;
                }

                if ($import->getType() === $type) {
                    return true;
                }

                continue;
            }

            if ($token->isComment()
                && Preg::match(
                    '/(?<![[:alnum:]\$_])(?<!\\\)'.$import->getShortName().'(?![[:alnum:]_])/i',
                    $token->getContent(),
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function removeUseDeclaration(
        Tokens $tokens,
        NamespaceUseAnalysis $useDeclaration,
        bool $forceCompleteRemoval = false
    ): void {
        [$start, $end] = ($useDeclaration->isInMulti() && !$forceCompleteRemoval)
            ? [$useDeclaration->getChunkStartIndex(), $useDeclaration->getChunkEndIndex()]
            : [$useDeclaration->getStartIndex(), $useDeclaration->getEndIndex()];
        $loopStartIndex = $useDeclaration->isInMulti() || $forceCompleteRemoval ? $end : $end - 1;

        for ($index = $loopStartIndex; $index >= $start; --$index) {
            if ($tokens[$index]->isComment()) {
                continue;
            }

            if (!$tokens[$index]->isWhitespace() || !str_contains($tokens[$index]->getContent(), "\n")) {
                $tokens->clearAt($index);

                continue;
            }

            // when multi line white space keep the line feed if the previous token is a comment
            $prevIndex = $tokens->getPrevNonWhitespace($index);

            if ($tokens[$prevIndex]->isComment()) {
                $content = $tokens[$index]->getContent();
                $tokens[$index] = new Token([\T_WHITESPACE, substr($content, strrpos($content, "\n"))]); // preserve indent only
            } else {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            }
        }

        // For multi-use import statements the tokens containing FQN were already removed in the loop above.
        // We need to clean up tokens around the ex-chunk to keep the correct syntax and achieve proper formatting.
        if (!$forceCompleteRemoval && $useDeclaration->isInMulti()) {
            $this->cleanUpAfterImportChunkRemoval($tokens, $useDeclaration);

            return;
        }

        if ($tokens[$useDeclaration->getEndIndex()]->equals(';')) { // do not remove `? >`
            $tokens->clearAt($useDeclaration->getEndIndex());
        }

        $this->cleanUpSurroundingNewLines($tokens, $useDeclaration);
    }

    /**
     * @param list<NamespaceUseAnalysis> $useDeclarations
     */
    private function removeUsesInSameNamespace(Tokens $tokens, array $useDeclarations, NamespaceAnalysis $namespaceDeclaration): void
    {
        $namespace = $namespaceDeclaration->getFullName();
        $nsLength = \strlen($namespace.'\\');

        foreach ($useDeclarations as $useDeclaration) {
            if ($useDeclaration->isAliased()) {
                continue;
            }

            $useDeclarationFullName = ltrim($useDeclaration->getFullName(), '\\');

            if (!str_starts_with($useDeclarationFullName, $namespace.'\\')) {
                continue;
            }

            $partName = substr($useDeclarationFullName, $nsLength);

            if (!str_contains($partName, '\\')) {
                $this->removeUseDeclaration($tokens, $useDeclaration);
            }
        }
    }

    private function cleanUpAfterImportChunkRemoval(Tokens $tokens, NamespaceUseAnalysis $useDeclaration): void
    {
        $beforeChunkIndex = $tokens->getPrevMeaningfulToken($useDeclaration->getChunkStartIndex());
        $afterChunkIndex = $tokens->getNextMeaningfulToken($useDeclaration->getChunkEndIndex());
        $hasNonEmptyTokenBefore = $this->scanForNonEmptyTokensUntilNewLineFound(
            $tokens,
            $afterChunkIndex,
            -1,
        );
        $hasNonEmptyTokenAfter = $this->scanForNonEmptyTokensUntilNewLineFound(
            $tokens,
            $afterChunkIndex,
            1,
        );

        // We don't want to merge consequent new lines with indentation (leading to e.g. `\n    \n    `),
        // so it's safe to merge whitespace only if there is any non-empty token before or after the chunk.
        $mergingSurroundingWhitespaceIsSafe = $hasNonEmptyTokenBefore[1] || $hasNonEmptyTokenAfter[1];
        $clearToken = static function (int $index) use ($tokens, $mergingSurroundingWhitespaceIsSafe): void {
            if ($mergingSurroundingWhitespaceIsSafe) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            } else {
                $tokens->clearAt($index);
            }
        };

        if ($tokens[$afterChunkIndex]->equals(',')) {
            $clearToken($afterChunkIndex);
        } elseif ($tokens[$beforeChunkIndex]->equals(',')) {
            $clearToken($beforeChunkIndex);
        }

        // Ensure there's a single space where applicable, otherwise no space (before comma, before closing brace)
        for ($index = $beforeChunkIndex; $index <= $afterChunkIndex; ++$index) {
            if (null === $tokens[$index]->getId() || !$tokens[$index]->isWhitespace(' ')) {
                continue;
            }

            $nextTokenIndex = $tokens->getNextMeaningfulToken($index);
            if (
                $tokens[$nextTokenIndex]->equals(',')
                || $tokens[$nextTokenIndex]->equals(';')
                || $tokens[$nextTokenIndex]->isGivenKind(CT::T_GROUP_IMPORT_BRACE_CLOSE)
            ) {
                $tokens->clearAt($index);
            } else {
                $tokens[$index] = new Token([\T_WHITESPACE, ' ']);
            }

            $prevTokenIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevTokenIndex]->isGivenKind(CT::T_GROUP_IMPORT_BRACE_OPEN)) {
                $tokens->clearAt($index);
            }
        }

        $this->removeLineIfEmpty($tokens, $useDeclaration);
        $this->removeImportStatementIfEmpty($tokens, $useDeclaration);
    }

    private function cleanUpSurroundingNewLines(Tokens $tokens, NamespaceUseAnalysis $useDeclaration): void
    {
        $prevIndex = $useDeclaration->getStartIndex() - 1;
        $prevToken = $tokens[$prevIndex];

        if ($prevToken->isWhitespace()) {
            $content = rtrim($prevToken->getContent(), " \t");

            $tokens->ensureWhitespaceAtIndex($prevIndex, 0, $content);

            $prevToken = $tokens[$prevIndex];
        }

        if (!isset($tokens[$useDeclaration->getEndIndex() + 1])) {
            return;
        }

        $nextIndex = $tokens->getNonEmptySibling($useDeclaration->getEndIndex(), 1);

        if (null === $nextIndex) {
            return;
        }

        $nextToken = $tokens[$nextIndex];

        if ($nextToken->isWhitespace()) {
            $content = Preg::replace(
                "#^\r\n|^\n#",
                '',
                ltrim($nextToken->getContent(), " \t"),
                1,
            );

            $tokens->ensureWhitespaceAtIndex($nextIndex, 0, $content);

            $nextToken = $tokens[$nextIndex];
        }

        if ($prevToken->isWhitespace() && $nextToken->isWhitespace()) {
            $content = $prevToken->getContent().$nextToken->getContent();

            $tokens->ensureWhitespaceAtIndex($nextIndex, 0, $content);

            $tokens->clearAt($prevIndex);
        }
    }

    private function removeImportStatementIfEmpty(Tokens $tokens, NamespaceUseAnalysis $useDeclaration): void
    {
        // First we look for empty groups where all chunks were removed (`use Foo\{};`).
        // We're only interested in ending brace if its index is between start and end of the import statement.
        $endingBraceIndex = $tokens->getPrevTokenOfKind(
            $useDeclaration->getEndIndex(),
            [[CT::T_GROUP_IMPORT_BRACE_CLOSE]],
        );

        if ($endingBraceIndex > $useDeclaration->getStartIndex()) {
            $openingBraceIndex = $tokens->getPrevMeaningfulToken($endingBraceIndex);

            if ($tokens[$openingBraceIndex]->isGivenKind(CT::T_GROUP_IMPORT_BRACE_OPEN)) {
                $this->removeUseDeclaration($tokens, $useDeclaration, true);
            }
        }

        // Second we look for empty groups where all comma-separated chunks were removed (`use;`).
        $beforeSemicolonIndex = $tokens->getPrevMeaningfulToken($useDeclaration->getEndIndex());
        if (
            $tokens[$beforeSemicolonIndex]->isGivenKind(\T_USE)
            || \in_array($tokens[$beforeSemicolonIndex]->getContent(), ['function', 'const'], true)
        ) {
            $this->removeUseDeclaration($tokens, $useDeclaration, true);
        }
    }

    private function removeLineIfEmpty(Tokens $tokens, NamespaceUseAnalysis $useAnalysis): void
    {
        if (!$useAnalysis->isInMulti()) {
            return;
        }

        $hasNonEmptyTokenBefore = $this->scanForNonEmptyTokensUntilNewLineFound(
            $tokens,
            $useAnalysis->getChunkStartIndex(),
            -1,
        );
        $hasNonEmptyTokenAfter = $this->scanForNonEmptyTokensUntilNewLineFound(
            $tokens,
            $useAnalysis->getChunkEndIndex(),
            1,
        );

        if (
            \is_int($hasNonEmptyTokenBefore[0])
            && !$hasNonEmptyTokenBefore[1]
            && \is_int($hasNonEmptyTokenAfter[0])
            && !$hasNonEmptyTokenAfter[1]
        ) {
            $tokens->clearRange($hasNonEmptyTokenBefore[0], $hasNonEmptyTokenAfter[0] - 1);
        }
    }

    /**
     * Returns tuple with the index of first token with whitespace containing new line char
     * and a flag if any non-empty token was found along the way.
     *
     * @param -1|1 $direction
     *
     * @return array{0: null|int, 1: bool}
     */
    private function scanForNonEmptyTokensUntilNewLineFound(Tokens $tokens, int $index, int $direction): array
    {
        $hasNonEmptyToken = false;
        $newLineTokenIndex = null;

        // Iterate until we find new line OR we get out of $tokens bounds (next sibling index is `null`).
        while (\is_int($index)) {
            $index = $tokens->getNonEmptySibling($index, $direction);

            if (null === $index || null === $tokens[$index]->getId()) {
                continue;
            }

            if (!$tokens[$index]->isWhitespace()) {
                $hasNonEmptyToken = true;
            } elseif (str_starts_with($tokens[$index]->getContent(), "\n")) {
                $newLineTokenIndex = $index;

                break;
            }
        }

        return [$newLineTokenIndex, $hasNonEmptyToken];
    }
}
