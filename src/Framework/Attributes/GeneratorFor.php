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
 * Registers a public static method of a test case class that returns a
 * generator as the generator for every value of exactly the given class that
 * a property of the test case class needs, including the values that are
 * needed to create objects of other classes with new.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class GeneratorFor
{
    /**
     * @var class-string
     */
    private string $type;

    /**
     * @param class-string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return class-string
     */
    public function type(): string
    {
        return $this->type;
    }
}
