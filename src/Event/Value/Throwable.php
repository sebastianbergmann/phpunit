<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use const PHP_EOL;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\Filter;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Throwable
{
    /**
     * @psalm-var class-string
     */
    private string $className;
    private string $message;
    private string $description;
    private string $stackTrace;
    private ?Throwable $previous;

    public static function from(\Throwable $t): self
    {
        $previous = $t->getPrevious();

        if ($previous !== null) {
            $previous = self::from($previous);
        }

        return new self(
            $t instanceof ExceptionWrapper ? $t->getClassName() : $t::class,
            $t->getMessage(),
            TestFailure::exceptionToString($t),
            Filter::getFilteredStacktrace($t),
            $previous
        );
    }

    /**
     * @psalm-param class-string $className
     */
    private function __construct(string $className, string $message, string $description, string $stackTrace, ?self $previous)
    {
        $this->className   = $className;
        $this->message     = $message;
        $this->description = $description;
        $this->stackTrace  = $stackTrace;
        $this->previous    = $previous;
    }

    public function asString(): string
    {
        if (empty($this->stackTrace())) {
            return $this->description();
        }

        return $this->description() . PHP_EOL . $this->stackTrace();
    }

    /**
     * @psalm-return $className
     */
    public function className(): string
    {
        return $this->className;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function stackTrace(): string
    {
        return $this->stackTrace;
    }

    /**
     * @psalm-assert-if-true !null $this->previous
     */
    public function hasPrevious(): bool
    {
        return $this->previous !== null;
    }

    /**
     * @throws NoPreviousThrowableException
     */
    public function previous(): self
    {
        if ($this->previous === null) {
            throw new NoPreviousThrowableException;
        }

        return $this->previous;
    }
}
