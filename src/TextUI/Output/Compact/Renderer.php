<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact;

use const PHP_EOL;
use function assert;
use function trim;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\TextUI\Output\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Renderer
{
    private Printer $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function nameOfTest(Test $test): string
    {
        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            if (!$test->testData()->hasDataFromDataProvider()) {
                return $test->nameWithClass();
            }

            return $test->className() . '::' . $test->methodName() . $test->testData()->dataFromDataProvider()->dataAsStringForResultOutput();
        }

        return $test->name();
    }

    public function printThrowable(Throwable $throwable): void
    {
        $this->printer->print(trim($throwable->description()) . PHP_EOL);
        $this->printStackTrace($throwable->stackTrace());

        if ($throwable->hasPrevious()) {
            $this->printer->print('Caused by' . PHP_EOL);
            $this->printThrowable($throwable->previous());
        }
    }

    public function printStackTrace(string $stackTrace): void
    {
        $stackTrace = trim($stackTrace);

        if ($stackTrace === '') {
            return;
        }

        $this->printer->print(PHP_EOL . $stackTrace . PHP_EOL);
    }
}
