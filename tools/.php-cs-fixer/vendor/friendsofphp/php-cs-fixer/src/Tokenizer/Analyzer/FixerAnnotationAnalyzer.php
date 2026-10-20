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

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Extracts @php-cs-fixer-* annotations from the given Tokens collection.
 *
 * Those annotations are controlling low-level PHP CS Fixer internal
 * are looked for only at the top and at the bottom of the file.
 * Any syntax of comment is allowed.
 *
 * @internal
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FixerAnnotationAnalyzer
{
    /**
     * @return array<string, list<string>>
     */
    public function find(Tokens $tokens): array
    {
        $comments = [];
        $annotations = [];

        $count = $tokens->count();
        $index = 0;

        for (0; $index < $count; ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind([
                \T_OPEN_TAG,
                \T_OPEN_TAG_WITH_ECHO,
                \T_WHITESPACE,
            ]) || $token->equals(';')) {
                continue;
            }

            if ($token->isGivenKind(\T_DECLARE)) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $nextIndex);

                continue;
            }

            if ($token->isComment()) {
                $comments[] = $token->getContent();

                continue;
            }

            break;
        }

        for ($indexBackwards = $count - 1; $indexBackwards > $index; --$indexBackwards) {
            $token = $tokens[$indexBackwards];

            if ($token->isGivenKind([
                \T_CLOSE_TAG,
                \T_WHITESPACE,
            ]) || $token->equals(';')) {
                continue;
            }

            if ($token->isComment()) {
                $comments[] = $token->getContent();

                continue;
            }

            break;
        }

        Preg::matchAll(
            '/^\h*[*\/]+\h+@(php-cs-fixer-\w+\h+(?:@?[\w\/,])+)/m',
            implode("\n", $comments),
            $matches,
        );

        foreach ($matches[1] as $match) {
            $matchParts = explode(' ', $match, 2);
            \assert(2 === \count($matchParts));

            $annotations[$matchParts[0]] ??= [];
            array_push($annotations[$matchParts[0]], ...explode(',', $matchParts[1]));
        }

        foreach ($annotations as $annotation => $vals) {
            $duplicates = array_keys(
                array_filter(
                    array_count_values($vals),
                    static fn (int $c): bool => $c > 1,
                ),
            );

            if (0 !== \count($duplicates)) {
                throw new \RuntimeException(\sprintf('Duplicated values found for annotation "@%s": "%s".', $annotation, implode('", "', $duplicates)));
            }

            sort($vals);
            $annotations[$annotation] = $vals;
        }

        return $annotations;
    }
}
