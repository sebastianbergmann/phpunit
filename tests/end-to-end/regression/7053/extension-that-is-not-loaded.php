<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
// Returns the name of an extension that is not loaded in the current process
// but can be loaded from the extension directory using "php -d extension=..."
// without PHP emitting output, or null when there is no such extension
return (static function (): ?string
{
    $suffix = '.' . \PHP_SHLIB_SUFFIX;
    $files  = \glob(\ini_get('extension_dir') . \DIRECTORY_SEPARATOR . '*' . $suffix);

    if ($files === false) {
        return null;
    }

    foreach ($files as $file) {
        $name = \basename($file, $suffix);

        if (\PHP_OS_FAMILY === 'Windows' && \str_starts_with($name, 'php_')) {
            $name = \substr($name, 4);
        }

        if (\extension_loaded($name)) {
            continue;
        }

        $process = \proc_open(
            [
                \PHP_BINARY,
                '-d',
                'extension=' . $name,
                '-r',
                'var_export(extension_loaded($argv[1]));',
                '--',
                $name,
            ],
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (!\is_resource($process)) {
            return null;
        }

        $stdout = \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);

        $stderr = \stream_get_contents($pipes[2]);
        \fclose($pipes[2]);

        \proc_close($process);

        if ($stdout === 'true' && $stderr === '') {
            return $name;
        }
    }

    return null;
})();
