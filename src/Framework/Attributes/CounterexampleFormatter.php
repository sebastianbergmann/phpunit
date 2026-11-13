<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * Specifies a public static method of the test class that describes the
 * counterexample of a property: when the property is falsified, the method is
 * called with the arguments of the counterexample, and the string that it
 * returns is reported below these arguments, which are reported as PHP code.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CounterexampleFormatter
{
    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @param non-empty-string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }
}
