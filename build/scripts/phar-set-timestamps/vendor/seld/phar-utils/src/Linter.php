<?php

/*
 * This file is part of PHAR Utils.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seld\PharUtils;

class Linter
{
    /**
     * Lints all php files inside a given phar with the current PHP version
     *
     * @param string $path Phar file path
     * @param list<string> $excludedPaths Paths which should be skipped by the linter
     */
    public static function lint($path, array $excludedPaths = array())
    {
        $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';

        if ($isWindows = defined('PHP_WINDOWS_VERSION_BUILD')) {
            $tmpFile = @tempnam(sys_get_temp_dir(), '');

            if (!$tmpFile || !is_writable($tmpFile)) {
                throw new \RuntimeException('Unable to create temp file');
            }

            $php = self::escapeWindowsPath($php);
            $tmpFile = self::escapeWindowsPath($tmpFile);

            // PHP 8 encloses the command in double-quotes
            if (PHP_VERSION_ID >= 80000) {
                $format = '%s -l %s';
            } else {
                $format = '"%s -l %s"';
            }

            $command = sprintf($format, $php, $tmpFile);
        } else {
            $command = "'".$php."' -l";
        }

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        // path to phar + phar:// + trailing slash
        $baseLen = strlen(realpath($path)) + 7 + 1;
        foreach (new \RecursiveIteratorIterator(new \Phar($path)) as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (substr($file, -4) === '.php') {
                $filename = (string) $file;
                if (in_array(substr($filename, $baseLen), $excludedPaths, true)) {
                    continue;
                }
                if ($isWindows) {
                    file_put_contents($tmpFile, file_get_contents($filename));
                }

                $process = proc_open($command, $descriptorspec, $pipes);
                if (is_resource($process)) {
                    if (!$isWindows) {
                        fwrite($pipes[0], file_get_contents($filename));
                    }
                    fclose($pipes[0]);

                    $stdout = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);

                    $exitCode = proc_close($process);

                    if ($exitCode !== 0) {
                        if ($isWindows) {
                            $stderr = str_replace($tmpFile, $filename, $stderr);
                        }
                        throw new \UnexpectedValueException('Failed linting '.$file.': '.$stderr);
                    }
                } else {
                    throw new \RuntimeException('Could not start linter process');
                }
            }
        }

        if ($isWindows) {
            @unlink($tmpFile);
        }
    }

    /**
     * Escapes a Windows file path
     *
     * @param string $path
     * @return string The escaped path
     */
    private static function escapeWindowsPath($path)
    {
        // Quote if path contains spaces or brackets
        if (strpbrk($path, " ()") !== false) {
            $path = '"'.$path.'"';
        }

        return $path;
    }
}
