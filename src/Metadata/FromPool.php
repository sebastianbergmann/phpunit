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
 * The pool from which a parameter of a method of a state machine receives a
 * value, which is specified with the #[FromPool] attribute of the parameter.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FromPool extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $parameterName;
    private string $pool;
    private bool $consume;

    /**
     * @param non-empty-string $parameterName
     */
    protected function __construct(Level $level, string $parameterName, string $pool, bool $consume)
    {
        parent::__construct($level);

        $this->parameterName = $parameterName;
        $this->pool          = $pool;
        $this->consume       = $consume;
    }

    public function isFromPool(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function parameterName(): string
    {
        return $this->parameterName;
    }

    public function pool(): string
    {
        return $this->pool;
    }

    /**
     * Whether the value is removed from the pool.
     */
    public function consume(): bool
    {
        return $this->consume;
    }
}
