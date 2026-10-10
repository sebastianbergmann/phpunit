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
 * Requires that at least the given percentage of the examples of a property
 * that are checked are classified with the given label by
 * TestCase::classify(). A property for which this is not the case is
 * considered risky.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class RequireClassification
{
    /**
     * @var non-empty-string
     */
    private string $label;
    private float $minimumPercentage;

    /**
     * @param non-empty-string $label
     */
    public function __construct(string $label, float $minimumPercentage)
    {
        $this->label             = $label;
        $this->minimumPercentage = $minimumPercentage;
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
