<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Prints what was recorded in a way that does not depend on where the test
 * fixture is on the file system, and that does not change when the hashing of
 * source files changes.
 */
function print_recorded_test_impact_data(string $file): void
{
    $data = \json_decode(\file_get_contents($file), true);

    \ksort($data['tests']);

    foreach ($data['tests'] as $test => $versions) {
        $files = [];

        foreach ($versions as $version) {
            $files[] = \basename($data['files'][$data['versions'][$version][0]]);
        }

        \sort($files);

        print $test . ' => ' . \implode(', ', $files) . \PHP_EOL;
    }
}
