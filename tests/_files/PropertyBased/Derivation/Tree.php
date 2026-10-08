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

final readonly class Tree
{
    /**
     * @var list<Tree>
     */
    private array $children;

    /**
     * @param list<Tree> $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }

    /**
     * @return list<Tree>
     */
    public function children(): array
    {
        return $this->children;
    }
}
