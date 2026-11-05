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
 * A method of a state machine that is called once before its steps are
 * taken, which is specified with the #[Initialize] attribute.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Initialize extends Metadata
{
    private ?string $produces;

    protected function __construct(Level $level, ?string $produces)
    {
        parent::__construct($level);

        $this->produces = $produces;
    }

    public function isInitialize(): true
    {
        return true;
    }

    /**
     * The name of the pool to which the value that the method returns is
     * added, null when it is not added to a pool.
     */
    public function produces(): ?string
    {
        return $this->produces;
    }
}
