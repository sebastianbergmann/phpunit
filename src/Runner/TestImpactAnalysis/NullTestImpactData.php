<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class NullTestImpactData implements TestImpactData
{
    /**
     * @param non-empty-string       $test
     * @param list<non-empty-string> $files
     */
    public function record(string $test, array $files): void
    {
    }

    /**
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function recorded(): array
    {
        return [];
    }
}
