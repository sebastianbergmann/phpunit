<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * A parameter of a rule or of a method with #[Initialize] of a state machine
 * with this attribute receives one of the values that earlier steps added to
 * the pool, rather than a generated value. With $consume, the value is
 * removed from the pool.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class FromPool
{
    /**
     * @var non-empty-string
     */
    private string $pool;
    private bool $consume;

    /**
     * @param non-empty-string $pool
     */
    public function __construct(string $pool, bool $consume = false)
    {
        $this->pool    = $pool;
        $this->consume = $consume;
    }

    /**
     * @return non-empty-string
     */
    public function pool(): string
    {
        return $this->pool;
    }

    public function consume(): bool
    {
        return $this->consume;
    }
}
