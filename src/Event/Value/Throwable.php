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

use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\Filter;

/**
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Throwable
{
    private string $message;

    private string $description;

    private string $stackTrace;

    public static function from(\Throwable $t): self
    {
        return new self(
            $t->getMessage(),
            TestFailure::exceptionToString($t),
            Filter::getFilteredStacktrace($t)
        );
    }

    private function __construct(string $message, string $description, string $stackTrace)
    {
        $this->message     = $message;
        $this->description = $description;
        $this->stackTrace  = $stackTrace;
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
}
