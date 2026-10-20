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

namespace PhpCsFixer\Fixer\NamespaceNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CleanNamespaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        $samples = [];

        foreach (['namespace Foo \ Bar;', 'echo foo /* comment */ \ bar();'] as $sample) {
            $samples[] = new VersionSpecificCodeSample(
                "<?php\n".$sample."\n",
                new VersionSpecification(null, 8_00_00 - 1),
            );
        }

        return new FixerDefinition(
            'Namespace must not contain spacing, comments or PHPDoc.',
            $samples,
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \PHP_VERSION_ID < 8_00_00 && $tokens->isTokenKindFound(\T_NS_SEPARATOR);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpUnitDataProviderReturnTypeFixer.
     */
    public function getPriority(): int
    {
        return 10;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $count = $tokens->count();

        for ($index = 0; $index < $count; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                $previousIndex = $tokens->getPrevMeaningfulToken($index);

                $index = $this->fixNamespace(
                    $tokens,
                    $tokens[$previousIndex]->isGivenKind(\T_STRING) ? $previousIndex : $index,
                );
            }
        }
    }

    /**
     * @param int $index start of namespace
     */
    private function fixNamespace(Tokens $tokens, int $index): int
    {
        $tillIndex = $index;

        // go to the end of the namespace
        while ($tokens[$tillIndex]->isGivenKind([\T_NS_SEPARATOR, \T_STRING])) {
            $tillIndex = $tokens->getNextMeaningfulToken($tillIndex);
        }

        $tillIndex = $tokens->getPrevMeaningfulToken($tillIndex);

        $spaceIndices = [];

        for (; $index <= $tillIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_WHITESPACE)) {
                $spaceIndices[] = $index;
            } elseif ($tokens[$index]->isComment()) {
                $tokens->clearAt($index);
            }
        }

        if ($tokens[$index - 1]->isWhitespace()) {
            array_pop($spaceIndices);
        }

        foreach ($spaceIndices as $i) {
            $tokens->clearAt($i);
        }

        return $index;
    }
}
