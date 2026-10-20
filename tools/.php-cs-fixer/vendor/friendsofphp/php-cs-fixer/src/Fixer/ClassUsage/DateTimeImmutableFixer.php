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

namespace PhpCsFixer\Fixer\ClassUsage;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DateTimeImmutableFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Class `DateTimeImmutable` should be used instead of `DateTime`.',
            [new CodeSample("<?php\nnew DateTime();\n")],
            null,
            'Risky when the code relies on modifying `DateTime` objects or if any of the `date_create*` functions are overridden.',
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();
        $functionMap = [
            'date_create' => 'date_create_immutable',
            'date_create_from_format' => 'date_create_immutable_from_format',
        ];

        $isInNamespace = false;
        $isImported = false; // e.g. use DateTime;

        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(\T_NAMESPACE)) {
                $isInNamespace = true;

                continue;
            }

            if ($isInNamespace && $token->isGivenKind(\T_USE)) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);

                if ('datetime' !== strtolower($tokens[$nextIndex]->getContent())) {
                    continue;
                }

                $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

                if ($tokens[$nextNextIndex]->equals(';')) {
                    $isImported = true;
                }

                $index = $nextNextIndex;

                continue;
            }

            if (!$token->isGivenKind(\T_STRING)) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);

            if ($tokens[$prevIndex]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $lowercaseContent = strtolower($token->getContent());

            if ('datetime' === $lowercaseContent) {
                $this->fixClassUsage($tokens, $index, $isInNamespace, $isImported);
                $limit = $tokens->count(); // update limit, as fixing class usage may insert new token

                continue;
            }

            if (isset($functionMap[$lowercaseContent]) && $functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                $tokens[$index] = new Token([\T_STRING, $functionMap[$lowercaseContent]]);
            }
        }
    }

    private function fixClassUsage(Tokens $tokens, int $index, bool $isInNamespace, bool $isImported): void
    {
        $nextIndex = $tokens->getNextMeaningfulToken($index);
        if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            if ($tokens[$nextNextIndex]->isGivenKind(\T_STRING)) {
                $nextNextNextIndex = $tokens->getNextMeaningfulToken($nextNextIndex);
                if (!$tokens[$nextNextNextIndex]->equals('(')) {
                    return;
                }
            }
        }

        $isUsedAlone = false; // e.g. new DateTime();
        $isUsedWithLeadingBackslash = false; // e.g. new \DateTime();

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $prevPrevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if (!$tokens[$prevPrevIndex]->isGivenKind(\T_STRING)) {
                $isUsedWithLeadingBackslash = true;
            }
        } elseif (!$tokens[$prevIndex]->isGivenKind(\T_DOUBLE_COLON) && !$tokens[$prevIndex]->isObjectOperator()) {
            $isUsedAlone = true;
        }

        if ($isUsedWithLeadingBackslash || $isUsedAlone && ($isInNamespace && $isImported || !$isInNamespace)) {
            $tokens[$index] = new Token([\T_STRING, \DateTimeImmutable::class]);
            if ($isInNamespace && $isUsedAlone) {
                $tokens->insertAt($index, new Token([\T_NS_SEPARATOR, '\\']));
            }
        }
    }
}
