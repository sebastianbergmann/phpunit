<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use Closure;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * The generator that is specified for a parameter of a property with the
 * #[ForAll] attribute of the parameter.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ForAll extends Metadata
{
    /**
     * @var non-empty-string
     */
    private string $parameterName;

    /**
     * @var Closure(): ValueGenerator<mixed>|non-empty-string
     */
    private Closure|string $generator;

    /**
     * @param non-empty-string                                  $parameterName
     * @param Closure(): ValueGenerator<mixed>|non-empty-string $generator
     */
    protected function __construct(Level $level, string $parameterName, Closure|string $generator)
    {
        parent::__construct($level);

        $this->parameterName = $parameterName;
        $this->generator     = $generator;
    }

    public function isForAll(): true
    {
        return true;
    }

    /**
     * @return non-empty-string
     */
    public function parameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * The name of a public static method of the test case class that returns
     * the generator, or a static closure that returns it.
     *
     * @return Closure(): ValueGenerator<mixed>|non-empty-string
     */
    public function generator(): Closure|string
    {
        return $this->generator;
    }
}
