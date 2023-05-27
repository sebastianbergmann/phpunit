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

use function mb_stripos;
use function mb_strtolower;
use function sprintf;
use function strpos;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringContains extends Constraint
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var bool
     */
    private $ignoreCase;

    public function __construct(string $string, bool $ignoreCase = false)
    {
        $this->string     = $string;
        $this->ignoreCase = $ignoreCase;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        if ($this->ignoreCase) {
            $string = mb_strtolower($this->string, 'UTF-8');
        } else {
            $string = $this->string;
        }

        return sprintf(
            'contains "%s"',
            $string,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        if ('' === $this->string) {
            return true;
        }

        if ($this->ignoreCase) {
            /*
             * We must use the multi byte safe version so we can accurately compare non latin upper characters with
             * their lowercase equivalents.
             */
            return mb_stripos($other, $this->string, 0, 'UTF-8') !== false;
        }

        /*
         * Use the non multi byte safe functions to see if the string is contained in $other.
         *
         * This function is very fast and we don't care about the character position in the string.
         *
         * Additionally, we want this method to be binary safe so we can check if some binary data is in other binary
         * data.
         */
        return strpos($other, $this->string) !== false;
    }
}
