<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Generator;

use function array_key_exists;
use function array_values;
use function strtolower;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DoubledMethodSet
{
    /**
     * @var array<string,DoubledMethod>
     */
    private array $methods = [];

    public function addMethods(DoubledMethod ...$methods): void
    {
        foreach ($methods as $method) {
            $this->methods[strtolower($method->methodName())] = $method;
        }
    }

    /**
     * @return list<DoubledMethod>
     */
    public function asArray(): array
    {
        return array_values($this->methods);
    }

    public function hasMethod(string $methodName): bool
    {
        return array_key_exists(strtolower($methodName), $this->methods);
    }
}
