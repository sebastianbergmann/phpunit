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

class Regex
{
    /**
     * @param non-empty-string $pattern
     */
    public static function isMatch(string $pattern, string $subject, int $offset = 0): bool
    {
        return (bool) Preg::match($pattern, $subject, $matches, 0, $offset);
    }

    /**
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function match(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchResult
    {
        self::checkOffsetCapture($flags, 'matchWithOffsets');

        $count = Preg::match($pattern, $subject, $matches, $flags, $offset);

        return new MatchResult($count, $matches);
    }

    /**
     * Variant of `match()` which returns non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @throws UnexpectedNullMatchException
     */
    public static function matchStrictGroups(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchStrictGroupsResult
    {
        // @phpstan-ignore composerPcre.maybeUnsafeStrictGroups
        $count = Preg::matchStrictGroups($pattern, $subject, $matches, $flags, $offset);

        return new MatchStrictGroupsResult($count, $matches);
    }

    /**
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     */
    public static function matchWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchWithOffsetsResult
    {
        $count = Preg::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);

        return new MatchWithOffsetsResult($count, $matches);
    }

    /**
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function matchAll(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchAllResult
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);

        $count = Preg::matchAll($pattern, $subject, $matches, $flags, $offset);

        return new MatchAllResult($count, $matches);
    }

    /**
     * Variant of `matchAll()` which returns non-null matches (or throws)
     *
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL> $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @throws UnexpectedNullMatchException
     */
    public static function matchAllStrictGroups(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchAllStrictGroupsResult
    {
        self::checkOffsetCapture($flags, 'matchAllWithOffsets');
        self::checkSetOrder($flags);

        // @phpstan-ignore composerPcre.maybeUnsafeStrictGroups
        $count = Preg::matchAllStrictGroups($pattern, $subject, $matches, $flags, $offset);

        return new MatchAllStrictGroupsResult($count, $matches);
    }

    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string $pattern
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     */
    public static function matchAllWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0): MatchAllWithOffsetsResult
    {
        self::checkSetOrder($flags);

        $count = Preg::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);

        return new MatchAllWithOffsetsResult($count, $matches);
    }
    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string          $subject
     */
    public static function replace($pattern, $replacement, $subject, int $limit = -1): ReplaceResult
    {
        $result = Preg::replace($pattern, $replacement, $subject, $limit, $count);

        return new ReplaceResult($count, $result);
    }

    /**
     * @param string|string[] $pattern
     * @param ($flags is PREG_OFFSET_CAPTURE ? (callable(array<int|string, array{string|null, int<-1, max>}>): string) : callable(array<int|string, string|null>): string) $replacement
     * @param string          $subject
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, int $flags = 0): ReplaceResult
    {
        $result = Preg::replaceCallback($pattern, $replacement, $subject, $limit, $count, $flags);

        return new ReplaceResult($count, $result);
    }

    /**
     * Variant of `replaceCallback()` which outputs non-null matches (or throws)
     *
     * @param string $pattern
     * @param ($flags is PREG_OFFSET_CAPTURE ? (callable(array<int|string, array{string, int<0, max>}>): string) : callable(array<int|string, string>): string) $replacement
     * @param string          $subject
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallbackStrictGroups($pattern, callable $replacement, $subject, int $limit = -1, int $flags = 0): ReplaceResult
    {
        $result = Preg::replaceCallbackStrictGroups($pattern, $replacement, $subject, $limit, $count, $flags);

        return new ReplaceResult($count, $result);
    }

    /**
     * @param ($flags is PREG_OFFSET_CAPTURE ? (array<string, callable(array<int|string, array{string|null, int<-1, max>}>): string>) : array<string, callable(array<int|string, string|null>): string>) $pattern
     * @param string $subject
     * @param int-mask<PREG_UNMATCHED_AS_NULL|PREG_OFFSET_CAPTURE> $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int $flags = 0): ReplaceResult
    {
        $result = Preg::replaceCallbackArray($pattern, $subject, $limit, $count, $flags);

        return new ReplaceResult($count, $result);
    }

    private static function checkOffsetCapture(int $flags, string $useFunctionName): void
    {
        if (($flags & PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use '.$useFunctionName.'() instead');
        }
    }

    private static function checkSetOrder(int $flags): void
    {
        if (($flags & PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the return type');
        }
    }
}
