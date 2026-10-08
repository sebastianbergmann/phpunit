<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use const SORT_STRING;
use function array_diff_key;
use function array_is_list;
use function array_keys;
use function count;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_scalar;
use function iterator_to_array;
use function realpath;
use function sort;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strcmp;
use function strlen;
use function substr;
use function usort;
use PHPUnit\Event\Emitter;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\TextUI\Configuration\TestFileResolver;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\Exception;
use PHPUnit\Util\Sanitizer;
use ReflectionObject;
use ReflectionProperty;
use Traversable;

/**
 * Shows the configuration that results from the defaults, the XML configuration
 * file, and the CLI options and arguments.
 *
 * The settings are found using reflection on the Configuration object, so that
 * a setting that is added to, changed in, or removed from it is shown without
 * changes to this command. Lists of files are shown after their include and
 * exclude rules have been applied by the code that a test run uses for this.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ShowEffectiveConfigurationCommand implements Command
{
    private const string INDENTATION = '  ';

    /**
     * Lists of files are not shown the way they are configured, but in a
     * section of their own after their include and exclude rules have been
     * applied.
     *
     * @var list<class-string>
     */
    private const array SHOWN_IN_SECTION_OF_THEIR_OWN = [
        FilterDirectoryCollection::class,
        FilterFileCollection::class,
        TestSuiteCollection::class,
    ];
    private Configuration $configuration;
    private Emitter $emitter;

    public function __construct(Configuration $configuration, Emitter $emitter)
    {
        $this->configuration = $configuration;
        $this->emitter       = $emitter;
    }

    public function execute(): Result
    {
        $shellExitCode = Result::SUCCESS;

        try {
            $testFiles = $this->section('Test files', $this->testFiles());
        } catch (Exception $e) {
            $testFiles     = ['Test files: ' . $this->string($e->getMessage())];
            $shellExitCode = Result::FAILURE;
        }

        $sourceFiles                = (new SourceMapper)->map($this->configuration->source());
        $sourceFilesForCodeCoverage = (new SourceMapper)->mapForCodeCoverage($this->configuration->source());

        $lines = [
            ...$this->entry('Settings', $this->configuration, 0),
            '',
            ...$testFiles,
            '',
            ...$this->section('Source files', $this->files(array_keys($sourceFiles))),
            '',
            ...$this->section('Source files excluded from code coverage', $this->files(array_keys(array_diff_key($sourceFiles, $sourceFilesForCodeCoverage)))),
            '',
            ...$this->section('PHAR extensions', $this->files($this->pharExtensions())),
        ];

        return Result::from(implode(PHP_EOL, $lines) . PHP_EOL, $shellExitCode);
    }

    /**
     * @param list<array{0: ?string, 1: mixed}> $children
     *
     * @return non-empty-list<string>
     */
    private function section(string $label, array $children): array
    {
        if ($children === []) {
            return [$label . ': (none)'];
        }

        return [$label . ':', ...$this->renderChildren($children, 1)];
    }

    /**
     * The test files are labelled with the name of their test suite, which is
     * not used as an array key because PHP turns a numeric string into an
     * integer when it is used as an array key.
     *
     * @throws Exception
     *
     * @return list<array{0: non-empty-string, 1: list<string>}>
     */
    private function testFiles(): array
    {
        $children = [];

        foreach (new TestFileResolver($this->emitter)->resolve($this->configuration) as $testSuite) {
            $files = [];

            foreach ($testSuite['files'] as $file) {
                $path = realpath($file['path']);

                // @codeCoverageIgnoreStart
                if ($path === false) {
                    $path = $file['path'];
                }
                // @codeCoverageIgnoreEnd

                $files[] = ['path' => $path, 'groups' => $file['groups']];
            }

            usort(
                $files,
                static fn (array $a, array $b): int => strcmp($a['path'], $b['path']),
            );

            $lines = [];

            foreach ($files as $file) {
                if ($file['groups'] === []) {
                    $lines[] = $file['path'];

                    continue;
                }

                $lines[] = sprintf(
                    '%s (groups: %s)',
                    $file['path'],
                    implode(', ', $file['groups']),
                );
            }

            $children[] = [$testSuite['name'], $lines];
        }

        return $children;
    }

    /**
     * @return list<non-empty-string>
     */
    private function pharExtensions(): array
    {
        if (!$this->configuration->hasPharExtensionDirectory()) {
            return [];
        }

        return new PharLoader($this->emitter)->pharFilesInDirectory(
            $this->configuration->pharExtensionDirectory(),
        );
    }

    /**
     * @param list<string> $paths
     *
     * @return list<array{0: null, 1: string}>
     */
    private function files(array $paths): array
    {
        sort($paths, SORT_STRING);

        $children = [];

        foreach ($paths as $path) {
            $children[] = [null, $path];
        }

        return $children;
    }

    /**
     * An item of a list has no label and is marked with "-" instead.
     *
     * @return non-empty-list<string>
     */
    private function entry(?string $label, mixed $value, int $depth): array
    {
        $value = $this->unwrap($value);

        if ($label === null) {
            $prefix = str_repeat(self::INDENTATION, $depth) . '-';
        } else {
            $prefix = str_repeat(self::INDENTATION, $depth) . $this->string($label) . ':';
        }

        if (!is_array($value) && !is_object($value)) {
            return [$prefix . ' ' . $this->scalar($value)];
        }

        $children = $this->children($value);

        if ($children === []) {
            return [$prefix . ' (none)'];
        }

        $lines = $this->renderChildren($children, $depth + 1);

        if ($label !== null) {
            return [$prefix, ...$lines];
        }

        // a list item that is not a scalar value starts on the line of its "-" marker
        $lines[0] = $prefix . ' ' . substr($lines[0], strlen(str_repeat(self::INDENTATION, $depth + 1)));

        return $lines;
    }

    /**
     * @param non-empty-list<array{0: ?string, 1: mixed}> $children
     *
     * @return non-empty-list<string>
     */
    private function renderChildren(array $children, int $depth): array
    {
        $lines = [];

        foreach ($children as [$label, $value]) {
            foreach ($this->entry($label, $value, $depth) as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * The items of a list or of a collection object are shown in their order
     * without labels, the elements of any other array are labelled with their
     * keys, and the properties of any other object are labelled with their
     * names and shown in alphabetical order.
     *
     * @param array<mixed>|object $value
     *
     * @return list<array{0: ?string, 1: mixed}>
     */
    private function children(array|object $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        $children = [];

        if (is_array($value)) {
            $isList = array_is_list($value);

            foreach ($value as $key => $element) {
                if ($isList) {
                    $children[] = [null, $element];
                } else {
                    $children[] = [(string) $key, $element];
                }
            }

            return $children;
        }

        foreach ($this->properties($value) as $property) {
            $propertyValue = $property->getValue($value);

            if ($this->isShownInSectionOfItsOwn($propertyValue)) {
                continue;
            }

            $children[] = [$property->getName(), $propertyValue];
        }

        return $children;
    }

    /**
     * An object that has exactly one property, such as an object that wraps a
     * path or an enumeration case that is not backed, is shown as the value of
     * that property.
     */
    private function unwrap(mixed $value): mixed
    {
        while (is_object($value) && !$value instanceof Traversable) {
            $properties = $this->properties($value);

            if (count($properties) !== 1) {
                break;
            }

            $value = $properties[0]->getValue($value);
        }

        return $value;
    }

    /**
     * @return list<ReflectionProperty>
     */
    private function properties(object $object): array
    {
        $properties = [];

        foreach (new ReflectionObject($object)->getProperties() as $property) {
            // @codeCoverageIgnoreStart
            if ($property->isStatic() || !$property->isInitialized($object)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            $properties[] = $property;
        }

        usort(
            $properties,
            static fn (ReflectionProperty $a, ReflectionProperty $b): int => strcmp($a->getName(), $b->getName()),
        );

        return $properties;
    }

    private function isShownInSectionOfItsOwn(mixed $value): bool
    {
        foreach (self::SHOWN_IN_SECTION_OF_THEIR_OWN as $className) {
            if ($value instanceof $className) {
                return true;
            }
        }

        return false;
    }

    private function scalar(mixed $value): string
    {
        if ($value === null) {
            return '(not set)';
        }

        if (is_bool($value)) {
            if ($value) {
                return 'true';
            }

            return 'false';
        }

        if ($value === '') {
            return '""';
        }

        if (is_scalar($value)) {
            return $this->string((string) $value);
        }

        // @codeCoverageIgnoreStart
        return '(cannot be shown)';
        // @codeCoverageIgnoreEnd
    }

    /**
     * A string from the configuration must neither hide nor fake parts of the
     * output, and a line break in it must not start a line of its own.
     */
    private function string(string $value): string
    {
        return Sanitizer::sanitizeControlCharacters(
            str_replace(["\r", "\n"], ['\u{000D}', '\u{000A}'], $value),
        );
    }
}
