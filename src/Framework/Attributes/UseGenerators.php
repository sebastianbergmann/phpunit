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
 * Uses the generators that the public static methods of the given class
 * register with #[GeneratorFor] for the properties of a test case class, or
 * for one property. A generator that the test case class registers takes
 * precedence over one that a class used for the property registers, which
 * takes precedence over one that a class used for the test case class
 * registers.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UseGenerators
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }
}
