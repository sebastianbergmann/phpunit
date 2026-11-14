<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Derivation;

use function array_values;

final readonly class ClassWithVariadicConstructor
{
    private int $first;

    /**
     * @var list<int>
     */
    private array $rest;

    public function __construct(int $first, int ...$rest)
    {
        $this->first = $first;
        $this->rest  = array_values($rest);
    }

    public function first(): int
    {
        return $this->first;
    }

    /**
     * @return list<int>
     */
    public function rest(): array
    {
        return $this->rest;
    }
}
