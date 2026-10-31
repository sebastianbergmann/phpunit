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
 * An invariant of a state machine, which is specified with the #[Invariant]
 * attribute.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Invariant extends Metadata
{
    private bool $atEnd;

    protected function __construct(Level $level, bool $atEnd)
    {
        parent::__construct($level);

        $this->atEnd = $atEnd;
    }

    public function isInvariant(): true
    {
        return true;
    }

    /**
     * Whether the invariant is only checked after the initialization and
     * after the last step rather than after every step.
     */
    public function atEnd(): bool
    {
        return $this->atEnd;
    }
}
