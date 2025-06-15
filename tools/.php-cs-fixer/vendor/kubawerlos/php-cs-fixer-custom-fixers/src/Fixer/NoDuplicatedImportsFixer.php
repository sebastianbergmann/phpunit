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

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\NamespaceUsesAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoDuplicatedImportsFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no duplicate `use` statements.',
            [new CodeSample('<?php
namespace FooBar;
use Foo;
use Foo;
use Bar;
')],
            '',
        );
    }

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
        $useDeclarations = (new NamespaceUsesAnalyzer())->getDeclarationsFromTokens($tokens);

        foreach ((new NamespacesAnalyzer())->getDeclarations($tokens) as $namespace) {
            $currentNamespaceUseDeclarations = \array_filter(
                $useDeclarations,
                static fn (NamespaceUseAnalysis $useDeclaration): bool => $useDeclaration->getStartIndex() >= $namespace->getScopeStartIndex()
                        && $useDeclaration->getEndIndex() <= $namespace->getScopeEndIndex(),
            );

            $foundDeclarations = [];
            foreach ($currentNamespaceUseDeclarations as $useDeclaration) {
                $key = \sprintf('key_%d_%s', $useDeclaration->getType(), $useDeclaration->getShortName());
                if (\in_array($key, $foundDeclarations, true)) {
                    $this->removeUseDeclaration($tokens, $useDeclaration);
                }
                $foundDeclarations[] = $key;
            }
        }
    }

    private function removeUseDeclaration(Tokens $tokens, NamespaceUseAnalysis $useDeclaration): void
    {
        $removeUseDeclaration = static function (
            NoUnusedImportsFixer $noUnusedImportsFixer,
            Tokens $tokens,
            NamespaceUseAnalysis $useDeclaration
        ): void {
            // @phpstan-ignore method.private
            $noUnusedImportsFixer->removeUseDeclaration($tokens, $useDeclaration);
        };

        $noUnusedImportsFixer = new NoUnusedImportsFixer();

        $removeUseDeclaration = \Closure::bind($removeUseDeclaration, null, $noUnusedImportsFixer);

        $removeUseDeclaration($noUnusedImportsFixer, $tokens, $useDeclaration);
    }
}
