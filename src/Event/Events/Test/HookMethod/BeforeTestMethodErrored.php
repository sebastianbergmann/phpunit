<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BeforeTestMethodErrored implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Code\TestMethod $test;
    private Code\ClassMethod $calledMethod;
    private Throwable $throwable;

    public function __construct(Telemetry\Info $telemetryInfo, Code\TestMethod $test, Code\ClassMethod $calledMethod, Throwable $throwable)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->test          = $test;
        $this->calledMethod  = $calledMethod;
        $this->throwable     = $throwable;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): Code\TestMethod
    {
        return $this->test;
    }

    /**
     * @return class-string
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/6140
     */
    public function testClassName(): string
    {
        return $this->test->className();
    }

    public function calledMethod(): Code\ClassMethod
    {
        return $this->calledMethod;
    }

    public function throwable(): Throwable
    {
        return $this->throwable;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $message = $this->throwable->message();

        if ($message !== '') {
            $message = PHP_EOL . $message;
        }

        return sprintf(
            'Before Test Method Errored (%s::%s)%s',
            $this->calledMethod->className(),
            $this->calledMethod->methodName(),
            $message,
        );
    }
}
