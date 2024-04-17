<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RunTestsInSeparateProcesses extends Metadata
{
    private ?bool $forkIfPossible;

    /**
     * @psalm-param 0|1 $level
     */
    protected function __construct(int $level, ?bool $forkIfPossible = null)
    {
        $this->forkIfPossible = $forkIfPossible;

        parent::__construct($level);
    }

    public function forkIfPossible(): ?bool
    {
        return $this->forkIfPossible;
    }

    /**
     * @psalm-assert-if-true RunTestsInSeparateProcesses $this
     */
    public function isRunTestsInSeparateProcesses(): bool
    {
        return true;
    }
}
