<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GlobalStateResult
{
    /**
     * @param list<array{name: non-empty-string, reason: non-empty-string}> $skippedGlobals
     */
    private string $globalsString;

    /**
     * @var list<array{name: non-empty-string, reason: non-empty-string}>
     */
    private array $skippedGlobals;

    /**
     * @param list<array{name: non-empty-string, reason: non-empty-string}> $skippedGlobals
     */
    public function __construct(string $globalsString, array $skippedGlobals)
    {
        $this->globalsString  = $globalsString;
        $this->skippedGlobals = $skippedGlobals;
    }

    public function globalsString(): string
    {
        return $this->globalsString;
    }

    /**
     * @return list<array{name: non-empty-string, reason: non-empty-string}>
     */
    public function skippedGlobals(): array
    {
        return $this->skippedGlobals;
    }

    public function hasSkippedGlobals(): bool
    {
        return $this->skippedGlobals !== [];
    }
}
