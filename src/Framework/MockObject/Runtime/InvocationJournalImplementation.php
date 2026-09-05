<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function count;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationJournalImplementation implements InvocationJournal
{
    /**
     * @var list<non-empty-string>
     */
    private array $invocations = [];

    /**
     * @param non-empty-string $label
     */
    public function record(string $label): void
    {
        $this->invocations[] = $label;
    }

    /**
     * @return list<non-empty-string>
     */
    public function invocations(): array
    {
        return $this->invocations;
    }

    public function count(): int
    {
        return count($this->invocations);
    }
}
