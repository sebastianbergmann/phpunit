<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * A label with which TestCase::target() targeted scores for examples of a
 * property, with the highest score that an example reached.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TargetedScore
{
    /**
     * @var non-empty-string
     */
    private string $label;
    private float|int $highestScore;

    /**
     * @param non-empty-string $label
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $label, float|int $highestScore)
    {
        $this->label        = $label;
        $this->highestScore = $highestScore;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->label;
    }

    public function highestScore(): float|int
    {
        return $this->highestScore;
    }
}
