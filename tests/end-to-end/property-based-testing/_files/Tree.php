<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

final readonly class Tree
{
    public int $value;

    /**
     * @var list<Tree>
     */
    public array $children;

    /**
     * @param list<Tree> $children
     */
    public function __construct(int $value, array $children)
    {
        $this->value    = $value;
        $this->children = $children;
    }

    /**
     * @return list<int>
     */
    public function values(): array
    {
        $values = [$this->value];

        foreach ($this->children as $child) {
            foreach ($child->values() as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
