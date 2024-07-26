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
final readonly class PreCondition extends Metadata
{
    /**
     * @var non-negative-int
     */
    private int $priority;

    /**
     * @param 0|1              $level
     * @param non-negative-int $priority
     */
    protected function __construct(int $level, int $priority)
    {
        parent::__construct($level);

        $this->priority = $priority;
    }

    public function isPreCondition(): true
    {
        return true;
    }

    /**
     * @return non-negative-int
     */
    public function priority(): int
    {
        return $this->priority;
    }
}
