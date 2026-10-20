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

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FullyQualifiedNameAnalyzer
{
    private Tokens $tokens;

    /**
     * @var list<NamespaceAnalysis>
     */
    private array $namespaceAnalyses = [];

    /**
     * @var array<int, list<NamespaceUseAnalysis>>
     */
    private array $namespaceUseAnalyses = [];

    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param NamespaceUseAnalysis::TYPE_* $importType
     */
    public function getFullyQualifiedName(string $name, int $indexInNamespace, int $importType): string
    {
        return ltrim($this->getFullyQualifiedNameWithPossiblyLeadingSlash($name, $indexInNamespace, $importType), '\\');
    }

    /**
     * @param NamespaceUseAnalysis::TYPE_* $importType
     */
    private function getFullyQualifiedNameWithPossiblyLeadingSlash(string $name, int $indexInNamespace, int $importType): string
    {
        if ('\\' === $name[0]) {
            return $name;
        }

        $namespaceAnalysis = $this->getNamespaceAnalysis($indexInNamespace);

        $this->namespaceUseAnalyses[$namespaceAnalysis->getStartIndex()] ??= (new NamespaceUsesAnalyzer())->getDeclarationsInNamespace($this->tokens, $namespaceAnalysis);
        \assert(isset($this->namespaceUseAnalyses[$namespaceAnalysis->getStartIndex()]));

        $declarations = [];
        foreach ($this->namespaceUseAnalyses[$namespaceAnalysis->getStartIndex()] as $namespaceUseAnalysis) {
            if ($namespaceUseAnalysis->getType() !== $importType) {
                continue;
            }
            $declarations[strtolower($namespaceUseAnalysis->getShortName())] = $namespaceUseAnalysis->getFullName();
        }

        $lowercaseName = strtolower($name);
        foreach ($declarations as $lowercaseShortName => $fullName) {
            if ($lowercaseName === $lowercaseShortName) {
                return $fullName;
            }

            if (!str_starts_with($lowercaseName, $lowercaseShortName.'\\')) {
                continue;
            }

            return $fullName.substr($name, \strlen($lowercaseShortName));
        }

        return $namespaceAnalysis->getFullName().'\\'.$name;
    }

    private function getNamespaceAnalysis(int $index): NamespaceAnalysis
    {
        foreach ($this->namespaceAnalyses as $namespace) {
            if ($namespace->getScopeStartIndex() <= $index && $namespace->getScopeEndIndex() >= $index) {
                return $namespace;
            }
        }

        $namespace = (new NamespacesAnalyzer())->getNamespaceAt($this->tokens, $index);

        $this->namespaceAnalyses[] = $namespace;

        return $namespace;
    }
}
