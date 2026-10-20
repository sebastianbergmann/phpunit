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

namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @deprecated
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ClassKeywordRemoveFixer extends AbstractFixer implements DeprecatedFixerInterface
{
    /**
     * @var array<array-key, string>
     */
    private array $imports = [];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts `::class` keywords to FQCN strings.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php

                        use Foo\Bar\Baz;

                        $className = Baz::class;

                        PHP,
                ),
            ],
        );
    }

    public function getSuccessorsNames(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoUnusedImportsFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(CT::T_CLASS_CONSTANT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $previousNamespaceScopeEndIndex = 0;
        foreach ($tokens->getNamespaceDeclarations() as $declaration) {
            $this->replaceClassKeywordsSection($tokens, '', $previousNamespaceScopeEndIndex, $declaration->getStartIndex());
            $this->replaceClassKeywordsSection($tokens, $declaration->getFullName(), $declaration->getStartIndex(), $declaration->getScopeEndIndex());
            $previousNamespaceScopeEndIndex = $declaration->getScopeEndIndex();
        }

        $this->replaceClassKeywordsSection($tokens, '', $previousNamespaceScopeEndIndex, $tokens->count() - 1);
    }

    private function storeImports(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $this->imports = [];

        foreach ($tokensAnalyzer->getImportUseIndexes() as $index) {
            if ($index < $startIndex || $index > $endIndex) {
                continue;
            }

            $import = '';
            while (($index = $tokens->getNextMeaningfulToken($index)) !== null) {
                if ($tokens[$index]->equalsAny([';', [CT::T_GROUP_IMPORT_BRACE_OPEN]]) || $tokens[$index]->isGivenKind(\T_AS)) {
                    break;
                }

                $import .= $tokens[$index]->getContent();
            }

            // Imports group (PHP 7 spec)
            if ($tokens[$index]->isGivenKind(CT::T_GROUP_IMPORT_BRACE_OPEN)) {
                $groupEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_GROUP_IMPORT_BRACE, $index);
                $groupImports = array_map(
                    static fn (string $import): string => trim($import),
                    explode(',', $tokens->generatePartialCode($index + 1, $groupEndIndex - 1)),
                );
                foreach ($groupImports as $groupImport) {
                    $groupImportParts = array_map(static fn (string $import): string => trim($import), explode(' as ', $groupImport));
                    if (2 === \count($groupImportParts)) {
                        $this->imports[$groupImportParts[1]] = $import.$groupImportParts[0];
                    } else {
                        $this->imports[] = $import.$groupImport;
                    }
                }
            } elseif ($tokens[$index]->isGivenKind(\T_AS)) {
                $aliasIndex = $tokens->getNextMeaningfulToken($index);
                $alias = $tokens[$aliasIndex]->getContent();
                $this->imports[$alias] = $import;
            } else {
                $this->imports[] = $import;
            }
        }
    }

    private function replaceClassKeywordsSection(Tokens $tokens, string $namespace, int $startIndex, int $endIndex): void
    {
        if ($endIndex - $startIndex < 3) {
            return;
        }

        $this->storeImports($tokens, $startIndex, $endIndex);

        $ctClassTokens = $tokens->findGivenKind(CT::T_CLASS_CONSTANT, $startIndex, $endIndex);
        foreach (array_reverse(array_keys($ctClassTokens)) as $classIndex) {
            $this->replaceClassKeyword($tokens, $namespace, $classIndex);
        }
    }

    private function replaceClassKeyword(Tokens $tokens, string $namespacePrefix, int $classIndex): void
    {
        $classEndIndex = $tokens->getPrevMeaningfulToken($classIndex);
        $classEndIndex = $tokens->getPrevMeaningfulToken($classEndIndex);

        if (!$tokens[$classEndIndex]->isGivenKind(\T_STRING)) {
            return;
        }

        if ($tokens[$classEndIndex]->equalsAny([[\T_STRING, 'self'], [\T_STATIC, 'static'], [\T_STRING, 'parent']], false)) {
            return;
        }

        $classBeginIndex = $classEndIndex;
        while (true) {
            $prev = $tokens->getPrevMeaningfulToken($classBeginIndex);
            if (!$tokens[$prev]->isGivenKind([\T_NS_SEPARATOR, \T_STRING])) {
                break;
            }

            $classBeginIndex = $prev;
        }

        $classString = $tokens->generatePartialCode(
            $tokens[$classBeginIndex]->isGivenKind(\T_NS_SEPARATOR)
                ? $tokens->getNextMeaningfulToken($classBeginIndex)
                : $classBeginIndex,
            $classEndIndex,
        );

        $classImport = false;
        if ($tokens[$classBeginIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $namespacePrefix = '';
        } else {
            foreach ($this->imports as $alias => $import) {
                if ($classString === $alias) {
                    $classImport = $import;

                    break;
                }

                $classStringArray = explode('\\', $classString);
                $namespaceToTest = $classStringArray[0];

                if (0 === ($namespaceToTest <=> substr($import, -\strlen($namespaceToTest)))) {
                    $classImport = $import;

                    break;
                }
            }
        }

        for ($i = $classBeginIndex; $i <= $classIndex; ++$i) {
            if (!$tokens[$i]->isComment() && !($tokens[$i]->isWhitespace() && str_contains($tokens[$i]->getContent(), "\n"))) {
                $tokens->clearAt($i);
            }
        }

        $tokens->insertAt($classBeginIndex, new Token([
            \T_CONSTANT_ENCAPSED_STRING,
            "'".$this->makeClassFQN($namespacePrefix, $classImport, $classString)."'",
        ]));
    }

    /**
     * @param false|string $classImport
     */
    private function makeClassFQN(string $namespacePrefix, $classImport, string $classString): string
    {
        if (false === $classImport) {
            return ('' !== $namespacePrefix ? ($namespacePrefix.'\\') : '').$classString;
        }

        $classStringArray = explode('\\', $classString);
        $classStringLength = \count($classStringArray);
        $classImportArray = explode('\\', $classImport);
        $classImportLength = \count($classImportArray);

        if (1 === $classStringLength) {
            return $classImport;
        }

        return implode('\\', array_merge(
            \array_slice($classImportArray, 0, $classImportLength - $classStringLength + 1),
            $classStringArray,
        ));
    }
}
