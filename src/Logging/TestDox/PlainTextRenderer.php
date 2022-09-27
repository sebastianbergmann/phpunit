<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function sprintf;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PlainTextRenderer
{
    /**
     * @psalm-param array<class-string, array{test: TestMethod, duration: Duration, status: TestStatus, throwable: ?Throwable, testDoubles: list<class-string|trait-string>}> $tests
     */
    public function render(array $tests): string
    {
        $buffer     = '';
        $prettifier = new NamePrettifier;

        foreach ($tests as $className => $_tests) {
            $buffer .= $prettifier->prettifyTestClass($className) . "\n";

            foreach ($_tests as $test) {
                $buffer .= sprintf(
                    ' [%s] %s' . "\n",
                    $test['status']->isSuccess() ? 'x' : ' ',
                    $prettifier->prettifyTestMethod($test['test']->methodName())
                );
            }

            $buffer .= "\n";
        }

        return $buffer;
    }
}
