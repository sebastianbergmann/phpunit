<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Constraint\Constraint;

final class NamedConstraint extends Constraint
{
    /**
     * @var int
     */
    private $name;

    public static function fromName(string $name): self
    {
        $instance = new self;

        $instance->name = $name;

        return $instance;
    }

    public function matches($other): bool
    {
        return true;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
