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

final readonly class Node
{
    private ?Node $left;
    private ?self $right;
    private int $value;

    public function __construct(?self $left, ?self $right, int $value)
    {
        $this->left  = $left;
        $this->right = $right;
        $this->value = $value;
    }

    public function left(): ?self
    {
        return $this->left;
    }

    public function right(): ?self
    {
        return $this->right;
    }

    public function value(): int
    {
        return $this->value;
    }
}
