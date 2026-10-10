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
 * A literal string, which is generated without a choice. An assertion such as
 * ^ or \b is the empty string.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class LiteralNode implements Node
{
    private string $literal;

    public function __construct(string $literal)
    {
        $this->literal = $literal;
    }

    public function generate(ChoiceSource $source): string
    {
        return $this->literal;
    }
}
