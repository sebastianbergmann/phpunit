<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

final class MockMethodSet
{
    /**
     * @var MockMethod[]
     */
    private $methods = [];

    public function addMethods(MockMethod ...$methods): void
    {
        foreach ($methods as $method) {
            $this->methods[\strtolower($method->getName())] = $method;
        }
    }

    public function asArray(): array
    {
        return \array_values($this->methods);
    }

    public function hasMethod(string $methodName): bool
    {
        return \array_key_exists(\strtolower($methodName), $this->methods);
    }
}
