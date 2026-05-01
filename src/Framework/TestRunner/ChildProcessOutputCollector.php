<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use const STDOUT;
use function ini_set;
use function rewind;
use function stream_get_contents;
use PHPUnit\Framework\TestCase;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChildProcessOutputCollector
{
    public static function collect(TestCase $test): string
    {
        $output = '';

        if (!$test->expectsOutput()) {
            $output = $test->output();
        }

        ini_set('xdebug.scream', '0');

        // Not every STDOUT target stream is rewindable
        $hasRewound = @rewind(STDOUT);

        if ($hasRewound && $stdout = @stream_get_contents(STDOUT)) {
            $output = $stdout . $output;
        }

        return $output;
    }
}
