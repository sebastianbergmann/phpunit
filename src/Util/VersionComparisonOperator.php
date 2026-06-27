<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use SebastianBergmann\VersionRequirement\InvalidVersionOperatorException as InvalidVersionOperatorExceptionImplementation;
use SebastianBergmann\VersionRequirement\VersionComparisonOperator as VersionComparisonOperatorImplementation;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class VersionComparisonOperator
{
    /**
     * @var '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne'
     */
    private string $operator;

    /**
     * @throws InvalidVersionOperatorException
     */
    public function __construct(string $operator)
    {
        try {
            $this->operator = new VersionComparisonOperatorImplementation($operator)->asString();
        } catch (InvalidVersionOperatorExceptionImplementation) {
            throw new InvalidVersionOperatorException($operator);
        }
    }

    /**
     * @return '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne'
     */
    public function asString(): string
    {
        return $this->operator;
    }
}
