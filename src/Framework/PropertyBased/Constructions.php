<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function assert;
use WeakMap;

/**
 * The objects that generators created with new while an example was
 * generated, together with the arguments that were passed to their
 * constructors. An object is exported as the expression that created it,
 * even when its state was changed afterwards.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Constructions
{
    /**
     * @var WeakMap<object, array{className: class-string, arguments: array<non-empty-string, mixed>}>
     */
    private WeakMap $constructions;

    public function __construct()
    {
        $this->constructions = new WeakMap;
    }

    /**
     * @param class-string                   $className
     * @param array<non-empty-string, mixed> $arguments the arguments by the names of the constructor's parameters
     */
    public function record(object $object, string $className, array $arguments): void
    {
        $this->constructions[$object] = ['className' => $className, 'arguments' => $arguments];
    }

    public function has(object $object): bool
    {
        return isset($this->constructions[$object]);
    }

    /**
     * @return array{className: class-string, arguments: array<non-empty-string, mixed>}
     */
    public function of(object $object): array
    {
        assert(isset($this->constructions[$object]));

        return $this->constructions[$object];
    }
}
