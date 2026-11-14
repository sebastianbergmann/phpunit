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
 * A method with this attribute is a property: a test method whose parameters
 * receive generated values. The property is checked for the given number of
 * examples, or the configured default number of examples when it is null.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Property
{
    /**
     * @var ?positive-int
     */
    private ?int $examples;

    /**
     * @param ?positive-int $examples
     */
    public function __construct(?int $examples = null)
    {
        $this->examples = $examples;
    }

    /**
     * @return ?positive-int
     */
    public function examples(): ?int
    {
        return $this->examples;
    }
}
