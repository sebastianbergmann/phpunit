<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\RegularExpression;

use function count;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;

/**
 * Alternatives of a regular expression, separated by |, of which the first
 * is the simplest one.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class AlternationNode implements Node
{
    /**
     * @var non-empty-list<Node>
     */
    private array $alternatives;
    private UniformDistribution $distribution;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-empty-list<Node> $alternatives
     */
    public function __construct(array $alternatives)
    {
        $this->label        = AbstractGenerator::labelOf('alternation', (string) count($alternatives));
        $this->alternatives = $alternatives;
        $this->distribution = new UniformDistribution;
    }

    public function generate(ChoiceSource $source): string
    {
        $source->startSpan($this->label);

        $string = $source->choose($this->alternatives, $this->distribution)->generate($source);

        $source->endSpan();

        return $string;
    }
}
