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
     * @param non-negative-int $index
     * @param non-empty-string $file
     */
    public function __construct(int $index, string $file)
    {
        $this->index = $index;
        $this->file  = $file;
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
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->file;
    }
}
