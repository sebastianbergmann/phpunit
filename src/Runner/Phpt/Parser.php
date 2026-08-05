<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use const DIRECTORY_SEPARATOR;
use function assert;
use function count;
use function dirname;
use function explode;
use function file;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function is_file;
use function is_readable;
use function is_string;
use function preg_match;
use function realpath;
use function rtrim;
use function sprintf;
use function str_contains;
use function trim;
use PHPUnit\Runner\Exception;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @see https://qa.php.net/phpt_details.php
 */
final readonly class Parser
{
    private const array SUPPORTED_SECTIONS = [
        'TEST',
        'FILE',
        'FILEEOF',
        'FILE_EXTERNAL',
        'EXPECT',
        'EXPECT_EXTERNAL',
        'EXPECTF',
        'EXPECTF_EXTERNAL',
        'EXPECTREGEX',
        'EXPECTREGEX_EXTERNAL',
        'INI',
        'ENV',
        'SKIPIF',
        'XFAIL',
        'CLEAN',
        'STDIN',
        'ARGS',
    ];
    private const array UNSUPPORTED_SECTIONS = [
        'CGI',
        'COOKIE',
        'DEFLATE_POST',
        'EXPECTHEADERS',
        'EXTENSIONS',
        'GET',
        'GZIP_POST',
        'HEADERS',
        'PHPDBG',
        'POST',
        'POST_RAW',
        'PUT',
        'REDIRECTTEST',
        'REQUEST',
    ];
    private const array IGNORED_SECTIONS = [
        'CAPTURE_STDIO',
        'CONFLICTS',
        'CREDITS',
        'DESCRIPTION',
        'FLAKY',
        'WHITESPACE_SENSITIVE',
        'XLEAK',
    ];
    private const array FILE_SECTIONS = [
        'FILE',
        'FILEEOF',
        'FILE_EXTERNAL',
    ];
    private const array EXPECTATION_SECTIONS = [
        'EXPECT',
        'EXPECT_EXTERNAL',
        'EXPECTF',
        'EXPECTF_EXTERNAL',
        'EXPECTREGEX',
        'EXPECTREGEX_EXTERNAL',
    ];

    /**
     * @param non-empty-string $phptFile
     *
     * @throws Exception
     *
     * @return array<non-empty-string, string>
     */
    public function parse(string $phptFile): array
    {
        $sections = [];
        $section  = '';

        $lines = @file($phptFile);

        if ($lines === false) {
            throw new CannotLoadPhptFileException;
        }

        $lineNr = 0;

        foreach ($lines as $line) {
            $lineNr++;

            if (preg_match('/^--([_A-Z]+)--/', $line, $result) === 1) {
                $section = $result[1];

                if (!in_array($section, self::SUPPORTED_SECTIONS, true) &&
                    !in_array($section, self::UNSUPPORTED_SECTIONS, true) &&
                    !in_array($section, self::IGNORED_SECTIONS, true)) {
                    throw new UnknownPhptSectionException($section);
                }

                if (isset($sections[$section])) {
                    throw new DuplicatePhptSectionException($section);
                }

                $sections[$section]             = '';
                $sections[$section . '_offset'] = (string) $lineNr;

                continue;
            }

            if ($section === '') {
                throw new InvalidPhptFileException(
                    sprintf(
                        'PHPT file must not contain text before its first section (line %d)',
                        $lineNr,
                    ),
                );
            }

            assert(isset($sections[$section]));

            $sections[$section] .= $line;
        }

        $codeSection = $this->ensureExactlyOneSectionOf($sections, self::FILE_SECTIONS);

        $this->ensureExactlyOneSectionOf($sections, self::EXPECTATION_SECTIONS);

        if (isset($sections['FILEEOF'])) {
            $sections['FILE'] = rtrim($sections['FILEEOF'], "\r\n");

            unset($sections['FILEEOF']);
        }

        $this->parseExternal($phptFile, $sections);

        foreach (self::UNSUPPORTED_SECTIONS as $unsupportedSection) {
            if (isset($sections[$unsupportedSection])) {
                throw new UnsupportedPhptSectionException($unsupportedSection);
            }
        }

        $this->ensureCodeIsNotEmpty($sections, $codeSection);

        return $sections;
    }

    /**
     * @return array<non-empty-string, string>
     */
    public function parseEnvSection(string $content): array
    {
        $env = [];

        foreach (explode("\n", trim($content)) as $e) {
            $e = explode('=', trim($e), 2);

            if ($e[0] !== '' && isset($e[1])) {
                $env[$e[0]] = $e[1];
            }
        }

        return $env;
    }

    /**
     * @param array<string>|string                          $content
     * @param array<non-empty-string, array<string>|string> $ini
     *
     * @return array<non-empty-string, array<string>|string>
     */
    public function parseIniSection(array|string $content, array $ini = []): array
    {
        if (is_string($content)) {
            $content = explode("\n", trim($content));
        }

        foreach ($content as $setting) {
            if (!str_contains($setting, '=')) {
                continue;
            }

            $setting = explode('=', $setting, 2);
            $name    = trim($setting[0]);

            if ($name === '' || !isset($setting[1])) {
                continue;
            }

            $value = trim($setting[1]);

            if ($name === 'extension' || $name === 'zend_extension') {
                if (!isset($ini[$name]) || !is_array($ini[$name])) {
                    $ini[$name] = [];
                }

                $ini[$name][] = $value;

                continue;
            }

            $ini[$name] = $value;
        }

        return $ini;
    }

    /**
     * @param non-empty-string                $phptFile
     * @param array<non-empty-string, string> $sections
     *
     * @throws Exception
     */
    private function parseExternal(string $phptFile, array &$sections): void
    {
        $allowSections = [
            'FILE',
            'EXPECT',
            'EXPECTF',
            'EXPECTREGEX',
        ];

        $testDirectory = dirname($phptFile) . DIRECTORY_SEPARATOR;

        foreach ($allowSections as $section) {
            if (isset($sections[$section . '_EXTERNAL'])) {
                $externalFilename = trim($sections[$section . '_EXTERNAL']);

                if (!is_file($testDirectory . $externalFilename) ||
                    !is_readable($testDirectory . $externalFilename)) {
                    throw new PhptExternalFileCannotBeLoadedException(
                        $section,
                        $testDirectory . $externalFilename,
                    );
                }

                $externalPath = $testDirectory . $externalFilename;
                $contents     = file_get_contents($externalPath);

                assert($contents !== false);

                $sections[$section] = $contents;

                if ($section === 'FILE') {
                    $resolvedPath = realpath($externalPath);

                    assert(is_string($resolvedPath));
                    assert($resolvedPath !== '');

                    $sections['FILE_EXTERNAL_PATH'] = $resolvedPath;
                }
            }
        }
    }

    /**
     * @param array<non-empty-string, string>  $sections
     * @param non-empty-list<non-empty-string> $sectionFamily
     *
     * @throws ConflictingPhptSectionsException
     * @throws InvalidPhptFileException
     *
     * @return non-empty-string
     */
    private function ensureExactlyOneSectionOf(array $sections, array $sectionFamily): string
    {
        $found = [];

        foreach ($sectionFamily as $name) {
            if (isset($sections[$name])) {
                $found[] = $name;
            }
        }

        if ($found === []) {
            throw new InvalidPhptFileException(
                sprintf(
                    'PHPT file must contain one of the sections --%s--',
                    implode('--, --', $sectionFamily),
                ),
            );
        }

        if (count($found) > 1) {
            throw new ConflictingPhptSectionsException($found);
        }

        return $found[0];
    }

    /**
     * @param array<non-empty-string, string> $sections
     * @param non-empty-string                $codeSection
     *
     * @throws InvalidPhptFileException
     */
    private function ensureCodeIsNotEmpty(array $sections, string $codeSection): void
    {
        if (isset($sections['FILE']) && $sections['FILE'] !== '') {
            return;
        }

        if ($codeSection === 'FILE_EXTERNAL') {
            throw new InvalidPhptFileException(
                'File referenced by --FILE_EXTERNAL-- section is empty',
            );
        }

        throw new InvalidPhptFileException(
            sprintf(
                '--%s-- section is empty',
                $codeSection,
            ),
        );
    }
}
