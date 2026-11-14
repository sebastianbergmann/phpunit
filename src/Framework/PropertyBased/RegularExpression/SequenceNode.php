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

use PHPUnit\Framework\PropertyBased\ChoiceSource;

/**
 * Parts of a regular expression that follow one another.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class SequenceNode implements Node
{
    /**
     * @var list<Node>
     */
    private array $nodes;

    /**
     * @param list<Node> $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function generate(ChoiceSource $source): string
    {
        $string = '';

        foreach ($this->nodes as $node) {
            $string .= $node->generate($source);
        }

        return $string;
    }
}
