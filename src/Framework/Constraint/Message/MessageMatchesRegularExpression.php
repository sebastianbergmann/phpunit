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

use function preg_match;
use function sprintf;
use Exception;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class MessageMatchesRegularExpression extends Constraint
{
    private readonly string $messageType;
    private readonly string $regularExpression;

    public function __construct(string $messageType, string $regularExpression)
    {
        $this->messageType       = $messageType;
        $this->regularExpression = $regularExpression;
    }

    public function toString(): string
    {
        return $this->messageType . ' message matches ';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws Exception
     */
    protected function matches(mixed $other): bool
    {
        $match = @preg_match($this->regularExpression, (string) $other);

        if ($match === false) {
            throw new \PHPUnit\Framework\Exception(
                sprintf(
                    'Invalid expected %s message regular expression given: %s',
                    $this->messageType,
                    $this->regularExpression
                )
            );
        }

        return $match === 1;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            "%s message '%s' matches '%s'",
            $this->messageType,
            $other,
            $this->regularExpression
        );
    }
}
