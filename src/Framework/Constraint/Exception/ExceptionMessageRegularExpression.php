<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function sprintf;
use Exception;
use PHPUnit\Util\RegularExpression as RegularExpressionUtil;

final class ExceptionMessageRegularExpression extends Constraint
{
    /**
     * @var string
     */
    private $expectedMessageRegExp;

    public function __construct(string $expected)
    {
        $this->expectedMessageRegExp = $expected;
    }

    public function toString(): string
    {
        return 'exception message matches ';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param \PHPUnit\Framework\Exception $other
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\Exception
     */
    protected function matches($other): bool
    {
        $match = RegularExpressionUtil::safeMatch($this->expectedMessageRegExp, $other->getMessage());

        if ($match === false) {
            throw new \PHPUnit\Framework\Exception(
                "Invalid expected exception message regex given: '{$this->expectedMessageRegExp}'"
            );
        }

        return $match === 1;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        return sprintf(
            "exception message '%s' matches '%s'",
            $other->getMessage(),
            $this->expectedMessageRegExp
        );
    }
}
