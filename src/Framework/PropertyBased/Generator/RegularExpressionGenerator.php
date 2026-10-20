<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function preg_match;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\RegularExpression\Node;

/**
 * Generates strings that a regular expression matches, from the nodes that
 * it was parsed into. A string that the regular expression does not match,
 * for instance because an anchor is neither at its start nor at its end, is
 * rejected.
 *
 * @template-extends AbstractGenerator<string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RegularExpressionGenerator extends AbstractGenerator
{
    /**
     * @var non-empty-string
     */
    private string $regularExpression;
    private Node $node;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-empty-string $regularExpression
     */
    public function __construct(string $regularExpression, Node $node)
    {
        $this->label = self::labelOf('regularExpression', $regularExpression);

        $this->regularExpression = $regularExpression;
        $this->node              = $node;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): string
    {
        $string = $this->node->generate($source);

        if (preg_match($this->regularExpression, $string) !== 1) {
            throw ExampleRejectedException::doesNotMatchRegularExpression($this->regularExpression);
        }

        return $string;
    }
}
