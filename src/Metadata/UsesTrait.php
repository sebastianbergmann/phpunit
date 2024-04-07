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
final readonly class UsesTrait extends Metadata
{
    /**
     * @psalm-var trait-string
     */
    private string $traitName;

    /**
     * @psalm-param 0|1 $level
     * @psalm-param trait-string $traitName
     */
    protected function __construct(int $level, string $traitName)
    {
        parent::__construct($level);

        $this->traitName = $traitName;
    }

    /**
     * @psalm-assert-if-true UsesTrait $this
     */
    public function isUsesTrait(): bool
    {
        return true;
    }

    /**
     * @psalm-return trait-string
     */
    public function traitName(): string
    {
        return $this->traitName;
    }

    /**
     * @psalm-return trait-string
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function asStringForCodeUnitMapper(): string
    {
        return $this->traitName;
    }
}
