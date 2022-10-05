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
     * @var null|string
     */
    protected $previousThrowableClass;

    /**
     * @var null|int|string
     */
    protected $previousThrowableCode;

    /**
     * @var null|string
     */
    protected $previousThrowableMessage;

    /**
     * @var null|string
     */
    protected $previousThrowableTrace;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, bool $inIsolation = false)
    {
        parent::__construct($message, $code, $previous);

        if ($inIsolation && $previous) {
            $this->previousThrowableClass   = get_class($previous);
            $this->previousThrowableMessage = $previous->getMessage();
            $this->previousThrowableCode    = $previous->getCode();

            try {
                $this->previousThrowableTrace = Filter::getFilteredStacktrace($previous);
            } catch (Exception $e) {
                $this->previousThrowableTrace = '';
            }
        }
    }

    public function __toString(): string
    {
        $string = parent::__toString();

        if ($this->previousThrowableClass) {
            $string .= PHP_EOL . 'Caused by ' . $this->previousThrowableClass;

            if ($this->previousThrowableMessage !== '') {
                $string .= ': ' . $this->previousThrowableMessage;
            }

            $string .= PHP_EOL . PHP_EOL . $this->previousThrowableTrace;
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
