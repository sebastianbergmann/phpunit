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

namespace PhpCsFixer\Doctrine\Annotation;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token as PhpToken;

/**
 * A list of Doctrine annotation tokens.
 *
 * @internal
 *
 * @extends \SplFixedArray<Token>
 *
 * `SplFixedArray` uses `T|null` in return types because value can be null if an offset is unset or if the size does not match the number of elements.
 * But our class takes care of it and always ensures correct size and indexes, so that these methods never return `null` instead of `Token`.
 *
 * @method Token                    offsetGet($offset)
 * @method \Traversable<int, Token> getIterator()
 * @method array<int, Token>        toArray()
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Tokens extends \SplFixedArray
{
    /**
     * @param list<string> $ignoredTags
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromDocComment(PhpToken $input, array $ignoredTags = []): self
    {
        if (!$input->isGivenKind(\T_DOC_COMMENT)) {
            throw new \InvalidArgumentException('Input must be a T_DOC_COMMENT token.');
        }

        $tokens = [];

        $content = $input->getContent();
        $ignoredTextPosition = 0;
        $currentPosition = 0;
        $token = null;
        while (false !== $nextAtPosition = strpos($content, '@', $currentPosition)) {
            if (0 !== $nextAtPosition && !Preg::match('/\s/', $content[$nextAtPosition - 1])) {
                $currentPosition = $nextAtPosition + 1;

                continue;
            }

            $lexer = new DocLexer();
            $lexer->setInput(substr($content, $nextAtPosition));

            $scannedTokens = [];
            $index = 0;
            $nbScannedTokensToUse = 0;
            $nbScopes = 0;
            while (null !== $token = $lexer->peek()) {
                if (0 === $index && !$token->isType(DocLexer::T_AT)) {
                    break;
                }

                if (1 === $index) {
                    if (!$token->isType(DocLexer::T_IDENTIFIER) || \in_array($token->getContent(), $ignoredTags, true)) {
                        break;
                    }

                    $nbScannedTokensToUse = 2;
                }

                if ($index >= 2 && 0 === $nbScopes && !$token->isType([DocLexer::T_NONE, DocLexer::T_OPEN_PARENTHESIS])) {
                    break;
                }

                $scannedTokens[] = $token;

                if ($token->isType(DocLexer::T_OPEN_PARENTHESIS)) {
                    ++$nbScopes;
                } elseif ($token->isType(DocLexer::T_CLOSE_PARENTHESIS)) {
                    if (0 === --$nbScopes) {
                        $nbScannedTokensToUse = \count($scannedTokens);

                        break;
                    }
                }

                ++$index;
            }

            if (0 !== $nbScopes) {
                break;
            }

            if (0 !== $nbScannedTokensToUse) {
                $ignoredTextLength = $nextAtPosition - $ignoredTextPosition;
                if (0 !== $ignoredTextLength) {
                    $tokens[] = new Token(DocLexer::T_NONE, substr($content, $ignoredTextPosition, $ignoredTextLength));
                }

                $lastTokenEndIndex = 0;
                foreach (\array_slice($scannedTokens, 0, $nbScannedTokensToUse) as $scannedToken) {
                    $token = $scannedToken->isType(DocLexer::T_STRING)
                        ? new Token(
                            $scannedToken->getType(),
                            '"'.str_replace('"', '""', $scannedToken->getContent()).'"',
                            $scannedToken->getPosition(),
                        )
                        : $scannedToken;

                    $missingTextLength = $token->getPosition() - $lastTokenEndIndex;
                    if ($missingTextLength > 0) {
                        $tokens[] = new Token(DocLexer::T_NONE, substr(
                            $content,
                            $nextAtPosition + $lastTokenEndIndex,
                            $missingTextLength,
                        ));
                    }

                    $tokens[] = new Token($token->getType(), $token->getContent());
                    $lastTokenEndIndex = $token->getPosition() + \strlen($token->getContent());
                }

                $currentPosition = $ignoredTextPosition = $nextAtPosition + $token->getPosition() + \strlen($token->getContent());
            } else {
                $currentPosition = $nextAtPosition + 1;
            }
        }

        if ($ignoredTextPosition < \strlen($content)) {
            $tokens[] = new Token(DocLexer::T_NONE, substr($content, $ignoredTextPosition));
        }

        return self::fromArray($tokens);
    }

    /**
     * Create token collection from array.
     *
     * @param array<int, Token> $array       the array to import
     * @param ?bool             $saveIndices save the numeric indices used in the original array, default is yes
     */
    public static function fromArray($array, $saveIndices = null): self
    {
        $tokens = new self(\count($array));

        if (null === $saveIndices || $saveIndices) {
            foreach ($array as $key => $val) {
                $tokens[$key] = $val;
            }
        } else {
            $index = 0;

            foreach ($array as $val) {
                $tokens[$index++] = $val;
            }
        }

        return $tokens;
    }

    /**
     * Returns the index of the closest next token that is neither a comment nor a whitespace token.
     */
    public function getNextMeaningfulToken(int $index): ?int
    {
        return $this->getMeaningfulTokenSibling($index, 1);
    }

    /**
     * Returns the index of the last token that is part of the annotation at the given index.
     */
    public function getAnnotationEnd(int $index): ?int
    {
        $currentIndex = null;

        if (isset($this[$index + 2])) {
            if ($this[$index + 2]->isType(DocLexer::T_OPEN_PARENTHESIS)) {
                $currentIndex = $index + 2;
            } elseif (
                isset($this[$index + 3])
                && $this[$index + 2]->isType(DocLexer::T_NONE)
                && $this[$index + 3]->isType(DocLexer::T_OPEN_PARENTHESIS)
                && Preg::match('/^(\R\s*\*\s*)*\s*$/', $this[$index + 2]->getContent())
            ) {
                $currentIndex = $index + 3;
            }
        }

        if (null !== $currentIndex) {
            $level = 0;
            for ($max = \count($this); $currentIndex < $max; ++$currentIndex) {
                if ($this[$currentIndex]->isType(DocLexer::T_OPEN_PARENTHESIS)) {
                    ++$level;
                } elseif ($this[$currentIndex]->isType(DocLexer::T_CLOSE_PARENTHESIS)) {
                    --$level;
                }

                if (0 === $level) {
                    return $currentIndex;
                }
            }

            return null;
        }

        return $index + 1;
    }

    /**
     * Returns the code from the tokens.
     */
    public function getCode(): string
    {
        $code = '';
        foreach ($this as $token) {
            $code .= $token->getContent();
        }

        return $code;
    }

    /**
     * Inserts a token at the given index.
     */
    public function insertAt(int $index, Token $token): void
    {
        $this->setSize($this->getSize() + 1);

        for ($i = $this->getSize() - 1; $i > $index; --$i) {
            $this[$i] = $this[$i - 1] ?? new Token();
        }

        $this[$index] = $token;
    }

    /**
     * @param null|int   $index
     * @param null|Token $token
     */
    public function offsetSet($index, $token): void
    {
        if (!$token instanceof Token) {
            throw new \InvalidArgumentException(\sprintf('Token must be an instance of %s, "%s" given.', Token::class, get_debug_type($token)));
        }

        parent::offsetSet($index, $token);
    }

    /**
     * @param mixed $index
     *
     * @throws \OutOfBoundsException
     */
    public function offsetUnset($index): void
    {
        if (!isset($this[$index])) {
            throw new \OutOfBoundsException(\sprintf('Index "%s" is invalid or does not exist.', $index));
        }

        $max = \count($this) - 1;
        while ($index < $max) {
            $this[$index] = $this[$index + 1];
            ++$index;
        }

        parent::offsetUnset($index);

        $this->setSize($max);
    }

    private function getMeaningfulTokenSibling(int $index, int $direction): ?int
    {
        while (true) {
            $index += $direction;

            if (!$this->offsetExists($index)) {
                break;
            }

            if (!$this[$index]->isType(DocLexer::T_NONE)) {
                return $index;
            }
        }

        return null;
    }
}
