<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Constraint\Constraint;

final class BooleanConstraint extends Constraint
{
    /**
     * @var int
     */
    private $matches;

    public static function fromBool(bool $matches): self
    {
        $instance = new self;

        $instance->matches = $matches;

        return $instance;
    }

    public function matches($other): bool
    {
        return $this->matches;
    }

    public function toString(): string
    {
        return $this->matches ? 'true' : 'false';
    }
}
