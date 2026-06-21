<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Version;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ConstraintRequirement extends Requirement
{
    private string $constraint;

    public function __construct(string $constraint)
    {
        $this->constraint = $constraint;
    }

    public function asString(): string
    {
        return $this->constraint;
    }
}
