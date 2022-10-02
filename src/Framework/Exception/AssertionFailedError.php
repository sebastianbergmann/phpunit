<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function get_class;
use PHPUnit\Util\Filter;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class AssertionFailedError extends Exception implements SelfDescribing
{
    /**
     * @var string|null
     */
    protected $previousThrowableClass;

    /**
     * @var string|null
     */
    protected $previousThrowableMessage;

    /**
     * @var int|null
     */
    protected $previousThrowableCode;

    /**
     * @var string|null
     */
    protected $previousThrowableTrace;

    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($previous) {
            $this->previousThrowableClass = get_class($previous);
            $this->previousThrowableMessage = $previous->getMessage();
            $this->previousThrowableCode = $previous->getCode();
            $this->previousThrowableTrace = Filter::getFilteredStacktrace($previous);
        }
    }

    public function __toString(): string
    {
        $string = parent::__toString();

        if ($this->previousThrowableClass) {
            $string .= "\nCaused by " . $this->previousThrowableClass;

            if ($this->previousThrowableMessage !== '') {
                $string .= ": " . $this->previousThrowableMessage;
            }

            $string .= "\n" . $this->previousThrowableTrace;
        }

        return $string;
    }

    /**
     * Wrapper for getMessage() which is declared as final.
     */
    public function toString(): string
    {
        return $this->getMessage();
    }
}
