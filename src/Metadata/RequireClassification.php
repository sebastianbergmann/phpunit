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
final readonly class RequireClassification extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $label;
    private float $minimumPercentage;

    /**
     * @param non-empty-string $label
     */
    protected function __construct(Level $level, string $label, float $minimumPercentage)
    {
        parent::__construct($level);

        $this->label             = $label;
        $this->minimumPercentage = $minimumPercentage;
    }

    public function isRequireClassification(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->label;
    }

    public function minimumPercentage(): float
    {
        return $this->minimumPercentage;
    }
}
