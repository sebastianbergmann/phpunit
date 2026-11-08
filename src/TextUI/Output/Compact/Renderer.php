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
use function str_replace;
use function trim;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\Sanitizer;

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

    /**
     * Prints the header line that starts a record ("--- TYPE: title").
     *
     * The header must always be exactly one line so that a user-supplied title cannot
     * start a record of its own. Line feeds and carriage returns in the title are therefore
     * made visible as escape sequences, in addition to the control characters that
     * Sanitizer::sanitizeControlCharacters() makes visible.
     */
    public function printHeader(string $type, string $title): void
    {
        $this->printer->print(PHP_EOL . '--- ' . $type . ': ' . $this->singleLine($title) . PHP_EOL);
    }

    /**
     * Prints one or more lines of user-supplied text that belong to the current record.
     */
    public function printBody(string $body): void
    {
        $this->printer->print(Sanitizer::sanitizeControlCharacters($body) . PHP_EOL);
    }

    public function printThrowable(Throwable $throwable): void
    {
        $this->printBody(trim($throwable->description()));
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

        $this->printer->print(PHP_EOL);
        $this->printBody($stackTrace);
    }

    private function singleLine(string $text): string
    {
        return str_replace(
            ["\r", "\n"],
            ['\u{000D}', '\u{000A}'],
            Sanitizer::sanitizeControlCharacters($text),
        );
    }
}
