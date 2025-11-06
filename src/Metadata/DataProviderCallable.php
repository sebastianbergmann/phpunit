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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataProviderCallable extends Metadata
{
    private \Closure $callable;

    private bool $validateArgumentCount;

    /**
     * @param int<0, 1> $level
     */
    protected function __construct(int $level, callable $callable, bool $validateArgumentCount)
    {
        parent::__construct($level);

        $this->callable = $callable;
        $this->validateArgumentCount = $validateArgumentCount;
    }

    public function isDataProvider(): true
    {
        return true;
    }

    public function callable(): callable {
        return $this->callable;
    }

    public function validateArgumentCount(): bool
    {
        return $this->validateArgumentCount;
    }
}
