<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheId;

/**
 * A unit of work for a single PHPT test, identified by the path of its .phpt
 * file. A PHPT test is not a PHPUnit\Framework\TestCase and carries no test
 * data, so the worker reconstructs it from nothing more than this file path.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PhptWorkUnit implements WorkUnit
{
    /**
     * @var non-negative-int
     */
    private int $index;

    /**
     * @var non-empty-string
     */
    private string $file;

    /**
     * @var list<non-empty-string>
     */
    private array $conflicts;

    /**
     * @param non-negative-int       $index
     * @param non-empty-string       $file
     * @param list<non-empty-string> $conflicts
     */
    public function __construct(int $index, string $file, array $conflicts = [])
    {
        $this->index     = $index;
        $this->file      = $file;
        $this->conflicts = $conflicts;
    }

    /**
     * @return non-negative-int
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * @return non-empty-string
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * The conflict keys declared by the test's --CONFLICTS-- section. While a
     * test that conflicts with key K is running, no other test that conflicts
     * with K may run. The reserved key "all" conflicts with every other test,
     * so a test that declares it runs on its own.
     *
     * @return list<non-empty-string>
     */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->file;
    }

    public function duration(ResultCache $resultCache): float
    {
        return $resultCache->time(
            ResultCacheId::fromReorderable(new PhptTestCase($this->file)),
        );
    }
}
