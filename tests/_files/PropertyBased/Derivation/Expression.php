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

final readonly class Expression
{
    public Expression|int $left;
    public Expression|int $right;

    public function __construct(int|self $left, int|self $right)
    {
        $this->left  = $left;
        $this->right = $right;
    }
}
