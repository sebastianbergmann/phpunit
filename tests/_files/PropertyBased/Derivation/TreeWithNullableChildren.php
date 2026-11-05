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

final readonly class TreeWithNullableChildren
{
    /**
     * @var list<?TreeWithNullableChildren>
     */
    private array $children;

    /**
     * @param list<?TreeWithNullableChildren> $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }

    /**
     * @return list<?TreeWithNullableChildren>
     */
    public function children(): array
    {
        return $this->children;
    }
}
