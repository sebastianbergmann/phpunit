<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Pcre;

class Preg
{
    /** @internal */
    public const ARRAY_MSG = '$subject as an array is not supported. You can use \'foreach\' instead.';
    /** @internal */
    public const INVALID_TYPE_MSG = '$subject must be a string, %s given.';

    /**
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|1
     *
     * @param-out array<int|string, string|null> $matches
     */
    public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        self::checkOffsetCapture($flags, 'matchWithOffsets');

        $result = preg_match($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL, $offset);
        if ($result === false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }

        return $result;
    }

    /**
     * Variant of `match()` which outputs non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|1
     * @throws UnexpectedNullMatchException
     *
     * @param-out array<int|string, string> $matches
     */
    public static function matchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        $result = self::match($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatches($pattern, $matchesInternal, 'match');

        return $result;
    }

    /**
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_UNMATCHED_AS_NULL and PREG_OFFSET_CAPTURE are always set, no other flags are supported
     * @return 0|1
     *
     * @param-out array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function matchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0): int
    {
        $result = preg_match($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE, $offset);
        if ($result === false) {
            throw PcreException::fromFunction('preg_match', $pattern);
        }

        return $result;
    }

    /**
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|positive-int
     *
     * @param-out array<int|string, list<string|null>> $matches
     */
    public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);

        $result = preg_match_all($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL, $offset);
        if (!is_int($result)) { // PHP < 8 may return null, 8+ returns int|false
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }

        return $result;
    }

    /**
     * Variant of `match()` which outputs non-null matches (or throws)
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|positive-int
     * @throws UnexpectedNullMatchException
     *
     * @param-out array<int|string, list<string>> $matches
     */
    public static function matchAllStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): int
    {
        $result = self::matchAll($pattern, $subject, $matchesInternal, $flags, $offset);
        $matches = self::enforceNonNullMatchAll($pattern, $matchesInternal, 'matchAll');

        return $result;
    }

    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     * @return 0|positive-int
     *
     * @param-out array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function matchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0): int
    {
        self::checkSetOrder($flags);

        $result = preg_match_all($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL | PREG_OFFSET_CAPTURE, $offset);
        if (!is_int($result)) { // PHP < 8 may return null, 8+ returns int|false
            throw PcreException::fromFunction('preg_match_all', $pattern);
        }

        return $result;
    }

    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string $subject
     * @param int             $count Set by method
     *
     * @param-out int<0, max> $count
     */
    public static function replace($pattern, $replacement, $subject, int $limit = -1, ?int &$count = null): string
    {
        if (!is_scalar($subject)) {
            if (is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }

            throw new \TypeError(sprintf(static::INVALID_TYPE_MSG, gettype($subject)));
        }

        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace', $pattern);
        }

        return $result;
    }

    /**
     * @param string|string[] $pattern
     * @param ($flags is PREG_OFFSET_CAPTURE ? (callable(array<int|string, array{string|null, int<-1, max>}>): string) : callable(array<int|string, string|null>): string) $replacement
     * @param string $subject
     * @param int             $count Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, ?int &$count = null, int $flags = 0): string
    {
        if (!is_scalar($subject)) {
            if (is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }

            throw new \TypeError(sprintf(static::INVALID_TYPE_MSG, gettype($subject)));
        }

        $result = preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags | PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            throw PcreException::fromFunction('preg_replace_callback', $pattern);
        }

        return $result;
    }

    /**
     * Variant of `replaceCallback()` which outputs non-null matches (or throws)
     *
     * @param string $pattern
     * @param ($flags is PREG_OFFSET_CAPTURE ? (callable(array<int|string, array{string, int<0, max>}>): string) : callable(array<int|string, string>): string) $replacement
     * @param string $subject
     * @param int $count Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallbackStrictGroups(string $pattern, callable $replacement, $subject, int $limit = -1, ?int &$count = null, int $flags = 0): string
    {
        return self::replaceCallback($pattern, function (array $matches) use ($pattern, $replacement) {
            return $replacement(self::enforceNonNullMatches($pattern, $matches, 'replaceCallback'));
        }, $subject, $limit, $count, $flags);
    }

    /**
     * @param ($flags is PREG_OFFSET_CAPTURE ? (array<string, callable(array<int|string, array{string|null, int<-1, max>}>): string>) : array<string, callable(array<int|string, string|null>): string>) $pattern
     * @param string $subject
     * @param int    $count Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     *
     * @param-out int<0, max> $count
     */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, ?int &$count = null, int $flags = 0): string
    {
        if (!is_scalar($subject)) {
            if (is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }

            throw new \TypeError(sprintf(static::INVALID_TYPE_MSG, gettype($subject)));
        }

        $result = preg_replace_callback_array($pattern, $subject, $limit, $count, $flags | PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            $pattern = array_keys($pattern);
            throw PcreException::fromFunction('preg_replace_callback_array', $pattern);
        }

        return $result;
    }

    /**
     * @param int-mask<PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_OFFSET_CAPTURE> $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE
     * @return list<string>
     */
    public static function split(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        if (($flags & PREG_SPLIT_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_SPLIT_OFFSET_CAPTURE is not supported as it changes the type of $matches, use splitWithOffsets() instead');
        }

        $result = preg_split($pattern, $subject, $limit, $flags);
        if ($result === false) {
            throw PcreException::fromFunction('preg_split', $pattern);
        }

        return $result;
    }

    /**
     * @param int-mask<PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_OFFSET_CAPTURE> $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE, PREG_SPLIT_OFFSET_CAPTURE is always set
     * @return list<array{string, int}>
     * @phpstan-return list<array{string, int<0, max>}>
     */
    public static function splitWithOffsets(string $pattern, string $subject, int $limit = -1, int $flags = 0): array
    {
        $result = preg_split($pattern, $subject, $limit, $flags | PREG_SPLIT_OFFSET_CAPTURE);
        if ($result === false) {
            throw PcreException::fromFunction('preg_split', $pattern);
        }

        return $result;
    }

    /**
     * @template T of string|\Stringable
     * @param string   $pattern
     * @param array<T> $array
     * @param int-mask<PREG_GREP_INVERT> $flags PREG_GREP_INVERT
     * @return array<T>
     */
    public static function grep(string $pattern, array $array, int $flags = 0): array
    {
        $result = preg_grep($pattern, $array, $flags);
        if ($result === false) {
            throw PcreException::fromFunction('preg_grep', $pattern);
        }

        return $result;
    }

    /**
     * Variant of match() which returns a bool instead of int
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @param-out array<int|string, string|null> $matches
     */
    public static function isMatch(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Variant of `isMatch()` which outputs non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @throws UnexpectedNullMatchException
     *
     * @param-out array<int|string, string> $matches
     */
    public static function isMatchStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) self::matchStrictGroups($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Variant of matchAll() which returns a bool instead of int
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @param-out array<int|string, list<string|null>> $matches
     */
    public static function isMatchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Variant of `isMatchAll()` which outputs non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @param-out array<int|string, list<string>> $matches
     */
    public static function isMatchAllStrictGroups(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0): bool
    {
        return (bool) self::matchAllStrictGroups($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Variant of matchWithOffsets() which returns a bool instead of int
     *
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @param-out array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function isMatchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }

    /**
     * Variant of matchAllWithOffsets() which returns a bool instead of int
     *
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<mixed> $matches Set by method
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @param-out array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function isMatchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0): bool
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }

    private static function checkOffsetCapture(int $flags, string $useFunctionName): void
    {
        if (($flags & PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use ' . $useFunctionName . '() instead');
        }
    }

    private static function checkSetOrder(int $flags): void
    {
        if (($flags & PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the type of $matches');
        }
    }

    /**
     * @param array<int|string, string|null|array{string|null, int}> $matches
     * @return array<int|string, string>
     * @throws UnexpectedNullMatchException
     */
    private static function enforceNonNullMatches(string $pattern, array $matches, string $variantMethod): array
    {
        foreach ($matches as $group => $match) {
            if (is_string($match) || (is_array($match) && is_string($match[0]))) {
                continue;
            }

            throw new UnexpectedNullMatchException('Pattern "'.$pattern.'" had an unexpected unmatched group "'.$group.'", make sure the pattern always matches or use '.$variantMethod.'() instead.');
        }

        /** @var array<string> */
        return $matches;
    }

    /**
     * @param array<int|string, list<string|null>> $matches
     * @return array<int|string, list<string>>
     * @throws UnexpectedNullMatchException
     */
    private static function enforceNonNullMatchAll(string $pattern, array $matches, string $variantMethod): array
    {
        foreach ($matches as $group => $groupMatches) {
            foreach ($groupMatches as $match) {
                if (null === $match) {
                    throw new UnexpectedNullMatchException('Pattern "'.$pattern.'" had an unexpected unmatched group "'.$group.'", make sure the pattern always matches or use '.$variantMethod.'() instead.');
                }
            }
        }

        /** @var array<int|string, list<string>> */
        return $matches;
    }
}
