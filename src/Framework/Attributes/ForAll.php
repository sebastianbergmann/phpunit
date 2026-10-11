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
use Closure;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * Specifies the generator for a parameter of a property: the name of a public
 * static method of the test case class that returns the generator, a list of
 * the name of a class and the name of a public static method of that class
 * that returns it, for instance [MoneyGenerators::class, 'smallAmounts'], or
 * a static closure that returns it.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ForAll
{
    /**
     * @var array{class-string, non-empty-string}|Closure(): ValueGenerator<mixed>|non-empty-string
     */
    private array|Closure|string $generator;

    /**
     * @param array{class-string, non-empty-string}|Closure(): ValueGenerator<mixed>|non-empty-string $generator
     */
    public function __construct(array|Closure|string $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return array{class-string, non-empty-string}|Closure(): ValueGenerator<mixed>|non-empty-string
     */
    public function generator(): array|Closure|string
    {
        return $this->generator;
    }
}
