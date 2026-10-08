<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Derivation;

use function assert;
use function ltrim;
use function strtolower;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\PropertyBased\ValueGeneratorNotSupportedException;

/**
 * The generators that a test case class registers with #[GeneratorFor] for
 * classes. Such a generator is used for every value of exactly that class.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GeneratorRegistry
{
    /**
     * @var array<lowercase-string, AbstractGenerator<mixed>>
     */
    private array $generators;

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param array<class-string, ValueGenerator<mixed>> $generators
     *
     * @throws ValueGeneratorNotSupportedException
     */
    public static function from(array $generators): self
    {
        $registered = [];

        foreach ($generators as $className => $generator) {
            $registered[self::key($className)] = AbstractGenerator::from($generator);
        }

        return new self($registered);
    }

    /**
     * @param array<lowercase-string, AbstractGenerator<mixed>> $generators
     */
    private function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    public function has(string $className): bool
    {
        return isset($this->generators[self::key($className)]);
    }

    /**
     * @return AbstractGenerator<mixed>
     */
    public function get(string $className): AbstractGenerator
    {
        $key = self::key($className);

        assert(isset($this->generators[$key]));

        return $this->generators[$key];
    }

    /**
     * Class names are case-insensitive.
     *
     * @return lowercase-string
     */
    private static function key(string $className): string
    {
        return strtolower(ltrim($className, '\\'));
    }
}
