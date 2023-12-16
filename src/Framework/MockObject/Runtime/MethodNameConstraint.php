<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function sprintf;
use function strtolower;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MethodNameConstraint extends Constraint
{
    private string $methodName;

    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function toString(): string
    {
        return sprintf(
            'is "%s"',
            $this->methodName,
        );
    }

    protected function matches(mixed $other): bool
    {
        return strtolower($this->methodName) === strtolower((string) $other);
    }
}
