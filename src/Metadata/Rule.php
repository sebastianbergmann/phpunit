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
 * A rule of a state machine, which is specified with the #[Rule] attribute.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Rule extends Metadata
{
    private ?string $precondition;
    private ?string $produces;

    protected function __construct(Level $level, ?string $precondition, ?string $produces)
    {
        parent::__construct($level);

        $this->precondition = $precondition;
        $this->produces     = $produces;
    }

    public function isRule(): true
    {
        return true;
    }

    /**
     * The name of the method of the state machine that tells whether the
     * rule can be taken, null when it can always be taken.
     */
    public function preconditionMethod(): ?string
    {
        return $this->precondition;
    }

    /**
     * The name of the pool to which the value that the rule returns is
     * added, null when it is not added to a pool.
     */
    public function produces(): ?string
    {
        return $this->produces;
    }
}
