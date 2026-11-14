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
 * A label with which TestCase::classify() classified examples of a property,
 * with the number of examples that were classified with it.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Classification
{
    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @var positive-int
     */
    private int $numberOfExamples;

    /**
     * @param non-empty-string $label
     * @param positive-int     $numberOfExamples
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $label, int $numberOfExamples)
    {
        $this->label            = $label;
        $this->numberOfExamples = $numberOfExamples;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return positive-int
     */
    public function numberOfExamples(): int
    {
        return $this->numberOfExamples;
    }
}
