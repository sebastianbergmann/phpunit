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

use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BeforeTestMethodCalled implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Code\TestMethod $test;
    private Code\ClassMethod $calledMethod;

    public function __construct(Telemetry\Info $telemetryInfo, Code\TestMethod $test, Code\ClassMethod $calledMethod)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->test          = $test;
        $this->calledMethod  = $calledMethod;
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

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Before Test Method Called (%s::%s)',
            $this->calledMethod->className(),
            $this->calledMethod->methodName(),
        );
    }
}
