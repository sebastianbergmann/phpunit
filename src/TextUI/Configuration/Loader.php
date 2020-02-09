<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Clover;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Crap4j;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Html as CodeCoverageHtml;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Php as CodeCoveragePhp;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Text as CodeCoverageText;
use PHPUnit\TextUI\Configuration\Logging\CodeCoverage\Xml as CodeCoverageXml;
use PHPUnit\TextUI\Configuration\Logging\Junit;
use PHPUnit\TextUI\Configuration\Logging\Logging;
use PHPUnit\TextUI\Configuration\Logging\PlainText;
use PHPUnit\TextUI\Configuration\Logging\TeamCity;
use PHPUnit\TextUI\Configuration\Logging\TestDox\Html as TestDoxHtml;
use PHPUnit\TextUI\Configuration\Logging\TestDox\Text as TestDoxText;
use PHPUnit\TextUI\Configuration\Logging\TestDox\Xml as TestDoxXml;
use PHPUnit\TextUI\Configuration\TestSuite as TestSuiteConfiguration;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use PHPUnit\Util\VersionComparisonOperator;
use PHPUnit\Util\Xml;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Loader
{
    public function load(string $filename): Configuration
    {
        $document = Xml::loadFile($filename, false, true, true);
        $xpath    = new \DOMXPath($document);

        return new Configuration(
            $filename,
            $this->validate($document),
            $this->extensions($filename, $xpath),
            $this->filter($filename, $xpath),
            $this->groups($xpath),
            $this->testdoxGroups($xpath),
            $this->listeners($filename, $xpath),
            $this->logging($filename, $xpath),
            $this->php($filename, $xpath),
            $this->phpunit($filename, $document),
            $this->testSuite($filename, $xpath)
        );
    }

    public function logging(string $filename, \DOMXPath $xpath): Logging
    {
        $codeCoverageClover = null;
        $codeCoverageCrap4j = null;
        $codeCoverageHtml   = null;
        $codeCoveragePhp    = null;
        $codeCoverageText   = null;
        $codeCoverageXml    = null;
        $junit              = null;
        $plainText          = null;
        $teamCity           = null;
        $testDoxHtml        = null;
        $testDoxText        = null;
        $testDoxXml         = null;

        foreach ($xpath->query('logging/log') as $log) {
            \assert($log instanceof \DOMElement);

            $type   = (string) $log->getAttribute('type');
            $target = (string) $log->getAttribute('target');

            if (!$target) {
                continue;
            }

            $target = $this->toAbsolutePath($filename, $target);

            switch ($type) {
                case 'coverage-clover':
                    $codeCoverageClover = new Clover(
                        new File($target)
                    );

                    break;

                case 'coverage-crap4j':
                    $codeCoverageCrap4j = new Crap4j(
                        new File($target),
                        $this->getIntegerAttribute($log, 'threshold', 30)
                    );

                    break;

                case 'coverage-html':
                    $codeCoverageHtml = new CodeCoverageHtml(
                        new Directory($target),
                        $this->getIntegerAttribute($log, 'lowUpperBound', 50),
                        $this->getIntegerAttribute($log, 'highLowerBound', 90)
                    );

                    break;

                case 'coverage-php':
                    $codeCoveragePhp = new CodeCoveragePhp(
                        new File($target)
                    );

                    break;

                case 'coverage-text':
                    $codeCoverageText = new CodeCoverageText(
                        new File($target),
                        $this->getBooleanAttribute($log, 'showUncoveredFiles', false),
                        $this->getBooleanAttribute($log, 'showOnlySummary', false)
                    );

                    break;

                case 'coverage-xml':
                    $codeCoverageXml = new CodeCoverageXml(
                        new Directory($target)
                    );

                    break;

                case 'plain':
                    $plainText = new PlainText(
                        new File($target)
                    );

                    break;

                case 'junit':
                    $junit = new Junit(
                        new File($target)
                    );

                    break;

                case 'teamcity':
                    $teamCity = new TeamCity(
                        new File($target)
                    );

                    break;

                case 'testdox-html':
                    $testDoxHtml = new TestDoxHtml(
                        new File($target)
                    );

                    break;

                case 'testdox-text':
                    $testDoxText = new TestDoxText(
                        new File($target)
                    );

                    break;

                case 'testdox-xml':
                    $testDoxXml = new TestDoxXml(
                        new File($target)
                    );

                    break;
            }
        }

        return new Logging(
            $codeCoverageClover,
            $codeCoverageCrap4j,
            $codeCoverageHtml,
            $codeCoveragePhp,
            $codeCoverageText,
            $codeCoverageXml,
            $junit,
            $plainText,
            $teamCity,
            $testDoxHtml,
            $testDoxText,
            $testDoxXml
        );
    }

    /**
     * @psalm-return array<int,array<int,string>>
     */
    private function validate(\DOMDocument $document): array
    {
        $original    = \libxml_use_internal_errors(true);
        $xsdFilename = __DIR__ . '/../../../phpunit.xsd';

        if (\defined('__PHPUNIT_PHAR_ROOT__')) {
            $xsdFilename =  __PHPUNIT_PHAR_ROOT__ . '/phpunit.xsd';
        }

        $document->schemaValidate($xsdFilename);
        $tmp = \libxml_get_errors();
        \libxml_clear_errors();
        \libxml_use_internal_errors($original);

        $errors = [];

        foreach ($tmp as $error) {
            if (!isset($errors[$error->line])) {
                $errors[$error->line] = [];
            }

            $errors[$error->line][] = \trim($error->message);
        }

        return $errors;
    }

    private function extensions(string $filename, \DOMXPath $xpath): ExtensionCollection
    {
        $extensions = [];

        foreach ($xpath->query('extensions/extension') as $extension) {
            \assert($extension instanceof \DOMElement);

            $extensions[] = $this->getElementConfigurationParameters($filename, $extension);
        }

        return ExtensionCollection::fromArray($extensions);
    }

    private function getElementConfigurationParameters(string $filename, \DOMElement $element): Extension
    {
        /** @psalm-var class-string $class */
        $class     = (string) $element->getAttribute('class');
        $file      = '';
        $arguments = $this->getConfigurationArguments($filename, $element->childNodes);

        if ($element->getAttribute('file')) {
            $file = $this->toAbsolutePath(
                $filename,
                (string) $element->getAttribute('file'),
                true
            );
        }

        return new Extension($class, $file, $arguments);
    }

    private function toAbsolutePath(string $filename, string $path, bool $useIncludePath = false): string
    {
        $path = \trim($path);

        if (\strpos($path, '/') === 0) {
            return $path;
        }

        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (\defined('PHP_WINDOWS_VERSION_BUILD') &&
            ($path[0] === '\\' || (\strlen($path) >= 3 && \preg_match('#^[A-Z]\:[/\\\]#i', \substr($path, 0, 3))))) {
            return $path;
        }

        if (\strpos($path, '://') !== false) {
            return $path;
        }

        $file = \dirname($filename) . \DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !\file_exists($file)) {
            $includePathFile = \stream_resolve_include_path($path);

            if ($includePathFile) {
                $file = $includePathFile;
            }
        }

        return $file;
    }

    private function getConfigurationArguments(string $filename, \DOMNodeList $nodes): array
    {
        $arguments = [];

        if ($nodes->length === 0) {
            return $arguments;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            if ($node->tagName !== 'arguments') {
                continue;
            }

            foreach ($node->childNodes as $argument) {
                if (!$argument instanceof \DOMElement) {
                    continue;
                }

                if ($argument->tagName === 'file' || $argument->tagName === 'directory') {
                    $arguments[] = $this->toAbsolutePath($filename, (string) $argument->textContent);
                } else {
                    $arguments[] = Xml::xmlToVariable($argument);
                }
            }
        }

        return $arguments;
    }

    private function filter(string $filename, \DOMXPath $xpath): Filter
    {
        $addUncoveredFilesFromWhitelist     = true;
        $processUncoveredFilesFromWhitelist = false;

        $nodes = $xpath->query('filter/whitelist');

        if ($nodes->length === 1) {
            $node = $nodes->item(0);

            \assert($node instanceof \DOMElement);

            if ($node->hasAttribute('addUncoveredFilesFromWhitelist')) {
                $addUncoveredFilesFromWhitelist = (bool) $this->getBoolean(
                    (string) $node->getAttribute('addUncoveredFilesFromWhitelist'),
                    true
                );
            }

            if ($node->hasAttribute('processUncoveredFilesFromWhitelist')) {
                $processUncoveredFilesFromWhitelist = (bool) $this->getBoolean(
                    (string) $node->getAttribute('processUncoveredFilesFromWhitelist'),
                    false
                );
            }
        }

        return new Filter(
            $this->readFilterDirectories($filename, $xpath, 'filter/whitelist/directory'),
            $this->readFilterFiles($filename, $xpath, 'filter/whitelist/file'),
            $this->readFilterDirectories($filename, $xpath, 'filter/whitelist/exclude/directory'),
            $this->readFilterFiles($filename, $xpath, 'filter/whitelist/exclude/file'),
            $addUncoveredFilesFromWhitelist,
            $processUncoveredFilesFromWhitelist
        );
    }

    /**
     * if $value is 'false' or 'true', this returns the value that $value represents.
     * Otherwise, returns $default, which may be a string in rare cases.
     * See PHPUnit\TextUI\ConfigurationTest::testPHPConfigurationIsReadCorrectly
     *
     * @param bool|string $default
     *
     * @return bool|string
     */
    private function getBoolean(string $value, $default)
    {
        if (\strtolower($value) === 'false') {
            return false;
        }

        if (\strtolower($value) === 'true') {
            return true;
        }

        return $default;
    }

    private function readFilterDirectories(string $filename, \DOMXPath $xpath, string $query): FilterDirectoryCollection
    {
        $directories = [];

        foreach ($xpath->query($query) as $directoryNode) {
            \assert($directoryNode instanceof \DOMElement);

            $directoryPath = (string) $directoryNode->textContent;

            if (!$directoryPath) {
                continue;
            }

            $directories[] = new FilterDirectory(
                $this->toAbsolutePath($filename, $directoryPath),
                $directoryNode->hasAttribute('prefix') ? (string) $directoryNode->getAttribute('prefix') : '',
                $directoryNode->hasAttribute('suffix') ? (string) $directoryNode->getAttribute('suffix') : '.php',
                $directoryNode->hasAttribute('group') ? (string) $directoryNode->getAttribute('group') : 'DEFAULT'
            );
        }

        return FilterDirectoryCollection::fromArray($directories);
    }

    private function readFilterFiles(string $filename, \DOMXPath $xpath, string $query): FilterFileCollection
    {
        $files = [];

        foreach ($xpath->query($query) as $file) {
            $filePath = (string) $file->textContent;

            if ($filePath) {
                $files[] = new FilterFile($this->toAbsolutePath($filename, $filePath));
            }
        }

        return FilterFileCollection::fromArray($files);
    }

    private function groups(\DOMXPath $xpath): Groups
    {
        return $this->parseGroupConfiguration($xpath, 'groups');
    }

    private function testdoxGroups(\DOMXPath $xpath): Groups
    {
        return $this->parseGroupConfiguration($xpath, 'testdoxGroups');
    }

    private function parseGroupConfiguration(\DOMXPath $xpath, string $root): Groups
    {
        $include = [];
        $exclude = [];

        foreach ($xpath->query($root . '/include/group') as $group) {
            $include[] = new Group((string) $group->textContent);
        }

        foreach ($xpath->query($root . '/exclude/group') as $group) {
            $exclude[] = new Group((string) $group->textContent);
        }

        return new Groups(
            GroupCollection::fromArray($include),
            GroupCollection::fromArray($exclude)
        );
    }

    private function listeners(string $filename, \DOMXPath $xpath): ExtensionCollection
    {
        $listeners = [];

        foreach ($xpath->query('listeners/listener') as $listener) {
            \assert($listener instanceof \DOMElement);

            $listeners[] = $this->getElementConfigurationParameters($filename, $listener);
        }

        return ExtensionCollection::fromArray($listeners);
    }

    private function getBooleanAttribute(\DOMElement $element, string $attribute, bool $default): bool
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return (bool) $this->getBoolean(
            (string) $element->getAttribute($attribute),
            false
        );
    }

    private function getIntegerAttribute(\DOMElement $element, string $attribute, int $default): int
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return $this->getInteger(
            (string) $element->getAttribute($attribute),
            $default
        );
    }

    private function getStringAttribute(\DOMElement $element, string $attribute): ?string
    {
        if (!$element->hasAttribute($attribute)) {
            return null;
        }

        return (string) $element->getAttribute($attribute);
    }

    private function getInteger(string $value, int $default): int
    {
        if (\is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function php(string $filename, \DOMXPath $xpath): Php
    {
        $includePaths = [];

        foreach ($xpath->query('php/includePath') as $includePath) {
            $path = (string) $includePath->textContent;

            if ($path) {
                $includePaths[] = new Directory($this->toAbsolutePath($filename, $path));
            }
        }

        $iniSettings = [];

        foreach ($xpath->query('php/ini') as $ini) {
            \assert($ini instanceof \DOMElement);

            $iniSettings[] = new IniSetting(
                (string) $ini->getAttribute('name'),
                (string) $ini->getAttribute('value')
            );
        }

        $constants = [];

        foreach ($xpath->query('php/const') as $const) {
            \assert($const instanceof \DOMElement);

            $value = (string) $const->getAttribute('value');

            $constants[] = new Constant(
                (string) $const->getAttribute('name'),
                $this->getBoolean($value, $value)
            );
        }

        $variables = [
            'var'     => [],
            'env'     => [],
            'post'    => [],
            'get'     => [],
            'cookie'  => [],
            'server'  => [],
            'files'   => [],
            'request' => [],
        ];

        foreach (['var', 'env', 'post', 'get', 'cookie', 'server', 'files', 'request'] as $array) {
            foreach ($xpath->query('php/' . $array) as $var) {
                \assert($var instanceof \DOMElement);

                $name     = (string) $var->getAttribute('name');
                $value    = (string) $var->getAttribute('value');
                $force    = false;
                $verbatim = false;

                if ($var->hasAttribute('force')) {
                    $force = (bool) $this->getBoolean($var->getAttribute('force'), false);
                }

                if ($var->hasAttribute('verbatim')) {
                    $verbatim = $this->getBoolean($var->getAttribute('verbatim'), false);
                }

                if (!$verbatim) {
                    $value = $this->getBoolean($value, $value);
                }

                $variables[$array][] = new Variable($name, $value, $force);
            }
        }

        return new Php(
            DirectoryCollection::fromArray($includePaths),
            IniSettingCollection::fromArray($iniSettings),
            ConstantCollection::fromArray($constants),
            VariableCollection::fromArray($variables['var']),
            VariableCollection::fromArray($variables['env']),
            VariableCollection::fromArray($variables['post']),
            VariableCollection::fromArray($variables['get']),
            VariableCollection::fromArray($variables['cookie']),
            VariableCollection::fromArray($variables['server']),
            VariableCollection::fromArray($variables['files']),
            VariableCollection::fromArray($variables['request']),
        );
    }

    private function phpunit(string $filename, \DOMDocument $document): PHPUnit
    {
        $executionOrder      = TestSuiteSorter::ORDER_DEFAULT;
        $defectsFirst        = false;
        $resolveDependencies = $this->getBooleanAttribute($document->documentElement, 'resolveDependencies', true);

        if ($document->documentElement->hasAttribute('executionOrder')) {
            foreach (\explode(',', $document->documentElement->getAttribute('executionOrder')) as $order) {
                switch ($order) {
                    case 'default':
                        $executionOrder      = TestSuiteSorter::ORDER_DEFAULT;
                        $defectsFirst        = false;
                        $resolveDependencies = true;

                        break;

                    case 'depends':
                        $resolveDependencies = true;

                        break;

                    case 'no-depends':
                        $resolveDependencies = false;

                        break;

                    case 'defects':
                        $defectsFirst = true;

                        break;

                    case 'duration':
                        $executionOrder = TestSuiteSorter::ORDER_DURATION;

                        break;

                    case 'random':
                        $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                        break;

                    case 'reverse':
                        $executionOrder = TestSuiteSorter::ORDER_REVERSED;

                        break;

                    case 'size':
                        $executionOrder = TestSuiteSorter::ORDER_SIZE;

                        break;
                }
            }
        }

        $printerClass                          = $this->getStringAttribute($document->documentElement, 'printerClass');
        $testdox                               = $this->getBooleanAttribute($document->documentElement, 'testdox', false);
        $conflictBetweenPrinterClassAndTestdox = false;

        if ($testdox) {
            if ($printerClass !== null) {
                $conflictBetweenPrinterClassAndTestdox = true;
            }

            $printerClass = CliTestDoxPrinter::class;
        }

        $cacheResultFile = $this->getStringAttribute($document->documentElement, 'cacheResultFile');

        if ($cacheResultFile !== null) {
            $cacheResultFile = $this->toAbsolutePath($filename, $cacheResultFile);
        }

        $bootstrap = $this->getStringAttribute($document->documentElement, 'bootstrap');

        if ($bootstrap !== null) {
            $bootstrap = $this->toAbsolutePath($filename, $bootstrap);
        }

        $extensionsDirectory = $this->getStringAttribute($document->documentElement, 'extensionsDirectory');

        if ($extensionsDirectory !== null) {
            $extensionsDirectory = $this->toAbsolutePath($filename, $extensionsDirectory);
        }

        $testSuiteLoaderFile = $this->getStringAttribute($document->documentElement, 'testSuiteLoaderFile');

        if ($testSuiteLoaderFile !== null) {
            $testSuiteLoaderFile = $this->toAbsolutePath($filename, $testSuiteLoaderFile);
        }

        $printerFile = $this->getStringAttribute($document->documentElement, 'printerFile');

        if ($printerFile !== null) {
            $printerFile = $this->toAbsolutePath($filename, $printerFile);
        }

        return new PHPUnit(
            $this->getBooleanAttribute($document->documentElement, 'cacheResult', false),
            $cacheResultFile,
            $this->getBooleanAttribute($document->documentElement, 'cacheTokens', false),
            $this->getColumns($document),
            $this->getColors($document),
            $this->getBooleanAttribute($document->documentElement, 'stderr', false),
            $this->getBooleanAttribute($document->documentElement, 'noInteraction', false),
            $this->getBooleanAttribute($document->documentElement, 'verbose', false),
            $this->getBooleanAttribute($document->documentElement, 'reverseDefectList', false),
            $this->getBooleanAttribute($document->documentElement, 'convertDeprecationsToExceptions', true),
            $this->getBooleanAttribute($document->documentElement, 'convertErrorsToExceptions', true),
            $this->getBooleanAttribute($document->documentElement, 'convertNoticesToExceptions', true),
            $this->getBooleanAttribute($document->documentElement, 'convertWarningsToExceptions', true),
            $this->getBooleanAttribute($document->documentElement, 'forceCoversAnnotation', false),
            $this->getBooleanAttribute($document->documentElement, 'ignoreDeprecatedCodeUnitsFromCodeCoverage', false),
            $this->getBooleanAttribute($document->documentElement, 'disableCodeCoverageIgnore', false),
            $bootstrap,
            $this->getBooleanAttribute($document->documentElement, 'processIsolation', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnWarning', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnRisky', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnDefect', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnError', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnFailure', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnWarning', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnIncomplete', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnRisky', false),
            $this->getBooleanAttribute($document->documentElement, 'stopOnSkipped', false),
            $extensionsDirectory,
            $this->getStringAttribute($document->documentElement, 'testSuiteLoaderClass'),
            $testSuiteLoaderFile,
            $printerClass,
            $printerFile,
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutChangesToGlobalState', false),
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutOutputDuringTests', false),
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutResourceUsageDuringSmallTests', false),
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutTestsThatDoNotTestAnything', true),
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutTodoAnnotatedTests', false),
            $this->getBooleanAttribute($document->documentElement, 'beStrictAboutCoversAnnotation', false),
            $this->getBooleanAttribute($document->documentElement, 'enforceTimeLimit', false),
            $this->getIntegerAttribute($document->documentElement, 'defaultTimeLimit', 1),
            $this->getIntegerAttribute($document->documentElement, 'timeoutForSmallTests', 1),
            $this->getIntegerAttribute($document->documentElement, 'timeoutForMediumTests', 10),
            $this->getIntegerAttribute($document->documentElement, 'timeoutForLargeTests', 60),
            $this->getStringAttribute($document->documentElement, 'defaultTestSuite'),
            $executionOrder,
            $resolveDependencies,
            $defectsFirst,
            $this->getBooleanAttribute($document->documentElement, 'backupGlobals', false),
            $this->getBooleanAttribute($document->documentElement, 'backupStaticAttributes', false),
            $this->getBooleanAttribute($document->documentElement, 'registerMockObjectsFromTestArgumentsRecursively', false),
            $conflictBetweenPrinterClassAndTestdox
        );
    }

    private function getColors(\DOMDocument $document): string
    {
        $colors = DefaultResultPrinter::COLOR_DEFAULT;

        if ($document->documentElement->hasAttribute('colors')) {
            /* only allow boolean for compatibility with previous versions
              'always' only allowed from command line */
            if ($this->getBoolean($document->documentElement->getAttribute('colors'), false)) {
                $colors = DefaultResultPrinter::COLOR_AUTO;
            } else {
                $colors = DefaultResultPrinter::COLOR_NEVER;
            }
        }

        return $colors;
    }

    /**
     * @return int|string
     */
    private function getColumns(\DOMDocument $document)
    {
        $columns = 80;

        if ($document->documentElement->hasAttribute('columns')) {
            $columns = (string) $document->documentElement->getAttribute('columns');

            if ($columns !== 'max') {
                $columns = $this->getInteger($columns, 80);
            }
        }

        return $columns;
    }

    private function testSuite(string $filename, \DOMXPath $xpath): TestSuiteCollection
    {
        $testSuites = [];

        foreach ($this->getTestSuiteElements($xpath) as $element) {
            $exclude = [];

            foreach ($element->getElementsByTagName('exclude') as $excludeNode) {
                $excludeFile = (string) $excludeNode->textContent;

                if ($excludeFile) {
                    $exclude[] = new File($this->toAbsolutePath($filename, $excludeFile));
                }
            }

            $directories = [];

            foreach ($element->getElementsByTagName('directory') as $directoryNode) {
                \assert($directoryNode instanceof \DOMElement);

                $directory = (string) $directoryNode->textContent;

                if (empty($directory)) {
                    continue;
                }

                $prefix = '';

                if ($directoryNode->hasAttribute('prefix')) {
                    $prefix = (string) $directoryNode->getAttribute('prefix');
                }

                $suffix = 'Test.php';

                if ($directoryNode->hasAttribute('suffix')) {
                    $suffix = (string) $directoryNode->getAttribute('suffix');
                }

                $phpVersion = \PHP_VERSION;

                if ($directoryNode->hasAttribute('phpVersion')) {
                    $phpVersion = (string) $directoryNode->getAttribute('phpVersion');
                }

                $phpVersionOperator = new VersionComparisonOperator('>=');

                if ($directoryNode->hasAttribute('phpVersionOperator')) {
                    $phpVersionOperator = new VersionComparisonOperator((string) $directoryNode->getAttribute('phpVersionOperator'));
                }

                $directories[] = new TestDirectory(
                    $this->toAbsolutePath($filename, $directory),
                    $prefix,
                    $suffix,
                    $phpVersion,
                    $phpVersionOperator
                );
            }

            $files = [];

            foreach ($element->getElementsByTagName('file') as $fileNode) {
                \assert($fileNode instanceof \DOMElement);

                $file = (string) $fileNode->textContent;

                if (empty($file)) {
                    continue;
                }

                $phpVersion = \PHP_VERSION;

                if ($fileNode->hasAttribute('phpVersion')) {
                    $phpVersion = (string) $fileNode->getAttribute('phpVersion');
                }

                $phpVersionOperator = new VersionComparisonOperator('>=');

                if ($fileNode->hasAttribute('phpVersionOperator')) {
                    $phpVersionOperator = new VersionComparisonOperator((string) $fileNode->getAttribute('phpVersionOperator'));
                }

                $files[] = new TestFile(
                    $this->toAbsolutePath($filename, $file),
                    $phpVersion,
                    $phpVersionOperator
                );
            }

            $testSuites[] = new TestSuiteConfiguration(
                (string) $element->getAttribute('name'),
                TestDirectoryCollection::fromArray($directories),
                TestFileCollection::fromArray($files),
                FileCollection::fromArray($exclude)
            );
        }

        return TestSuiteCollection::fromArray($testSuites);
    }

    /**
     * @return \DOMElement[]
     */
    private function getTestSuiteElements(\DOMXPath $xpath): array
    {
        /** @var \DOMElement[] $elements */
        $elements = [];

        $testSuiteNodes = $xpath->query('testsuites/testsuite');

        if ($testSuiteNodes->length === 0) {
            $testSuiteNodes = $xpath->query('testsuite');
        }

        if ($testSuiteNodes->length === 1) {
            $element = $testSuiteNodes->item(0);

            \assert($element instanceof \DOMElement);

            $elements[] = $element;
        } else {
            foreach ($testSuiteNodes as $testSuiteNode) {
                \assert($testSuiteNode instanceof \DOMElement);

                $elements[] = $testSuiteNode;
            }
        }

        return $elements;
    }
}
