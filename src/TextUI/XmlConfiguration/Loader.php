<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION;
use function assert;
use function defined;
use function dirname;
use function explode;
use function is_file;
use function is_numeric;
use function preg_match;
use function stream_resolve_include_path;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\Directory as FilterDirectory;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\DirectoryCollection as FilterDirectoryCollection;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Clover;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Cobertura;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Crap4j;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Html as CodeCoverageHtml;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php as CodeCoveragePhp;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Text as CodeCoverageText;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Xml as CodeCoverageXml;
use PHPUnit\TextUI\XmlConfiguration\Logging\Junit;
use PHPUnit\TextUI\XmlConfiguration\Logging\Logging;
use PHPUnit\TextUI\XmlConfiguration\Logging\TeamCity;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Html as TestDoxHtml;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Text as TestDoxText;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Xml as TestDoxXml;
use PHPUnit\TextUI\XmlConfiguration\Logging\Text;
use PHPUnit\TextUI\XmlConfiguration\TestSuite as TestSuiteConfiguration;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use PHPUnit\Util\VersionComparisonOperator;
use PHPUnit\Util\Xml;
use PHPUnit\Util\Xml\Exception as XmlException;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\SchemaFinder;
use PHPUnit\Util\Xml\Validator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Loader
{
    /**
     * @throws Exception
     */
    public function load(string $filename): Configuration
    {
        try {
            $document = (new XmlLoader)->loadFile($filename, false, true, true);
        } catch (XmlException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $xpath = new DOMXPath($document);

        try {
            $xsdFilename = (new SchemaFinder)->find(Version::series());
        } catch (XmlException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return new Configuration(
            $filename,
            (new Validator)->validate($document, $xsdFilename),
            $this->extensions($filename, $xpath),
            $this->codeCoverage($filename, $xpath, $document),
            $this->groups($xpath),
            $this->testdoxGroups($xpath),
            $this->listeners($filename, $xpath),
            $this->logging($filename, $xpath),
            $this->php($filename, $xpath),
            $this->phpunit($filename, $document),
            $this->testSuite($filename, $xpath)
        );
    }

    public function logging(string $filename, DOMXPath $xpath): Logging
    {
        if ($xpath->query('logging/log')->length !== 0) {
            return $this->legacyLogging($filename, $xpath);
        }

        $junit   = null;
        $element = $this->element($xpath, 'logging/junit');

        if ($element) {
            $junit = new Junit(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $text    = null;
        $element = $this->element($xpath, 'logging/text');

        if ($element) {
            $text = new Text(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $teamCity = null;
        $element  = $this->element($xpath, 'logging/teamcity');

        if ($element) {
            $teamCity = new TeamCity(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $testDoxHtml = null;
        $element     = $this->element($xpath, 'logging/testdoxHtml');

        if ($element) {
            $testDoxHtml = new TestDoxHtml(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $testDoxText = null;
        $element     = $this->element($xpath, 'logging/testdoxText');

        if ($element) {
            $testDoxText = new TestDoxText(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $testDoxXml = null;
        $element    = $this->element($xpath, 'logging/testdoxXml');

        if ($element) {
            $testDoxXml = new TestDoxXml(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        return new Logging(
            $junit,
            $text,
            $teamCity,
            $testDoxHtml,
            $testDoxText,
            $testDoxXml
        );
    }

    public function legacyLogging(string $filename, DOMXPath $xpath): Logging
    {
        $junit       = null;
        $teamCity    = null;
        $testDoxHtml = null;
        $testDoxText = null;
        $testDoxXml  = null;
        $text        = null;

        foreach ($xpath->query('logging/log') as $log) {
            assert($log instanceof DOMElement);

            $type   = (string) $log->getAttribute('type');
            $target = (string) $log->getAttribute('target');

            if (!$target) {
                continue;
            }

            $target = $this->toAbsolutePath($filename, $target);

            switch ($type) {
                case 'plain':
                    $text = new Text(
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
            $junit,
            $text,
            $teamCity,
            $testDoxHtml,
            $testDoxText,
            $testDoxXml
        );
    }

    private function extensions(string $filename, DOMXPath $xpath): ExtensionCollection
    {
        $extensions = [];

        foreach ($xpath->query('extensions/extension') as $extension) {
            assert($extension instanceof DOMElement);

            $extensions[] = $this->getElementConfigurationParameters($filename, $extension);
        }

        return ExtensionCollection::fromArray($extensions);
    }

    private function getElementConfigurationParameters(string $filename, DOMElement $element): Extension
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
        $path = trim($path);

        if (strpos($path, '/') === 0) {
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
        if (defined('PHP_WINDOWS_VERSION_BUILD') &&
            ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]\:[/\\\]#i', substr($path, 0, 3))))) {
            return $path;
        }

        if (strpos($path, '://') !== false) {
            return $path;
        }

        $file = dirname($filename) . DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !is_file($file)) {
            $includePathFile = stream_resolve_include_path($path);

            if ($includePathFile) {
                $file = $includePathFile;
            }
        }

        return $file;
    }

    private function getConfigurationArguments(string $filename, DOMNodeList $nodes): array
    {
        $arguments = [];

        if ($nodes->length === 0) {
            return $arguments;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if ($node->tagName !== 'arguments') {
                continue;
            }

            foreach ($node->childNodes as $argument) {
                if (!$argument instanceof DOMElement) {
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

    private function codeCoverage(string $filename, DOMXPath $xpath, DOMDocument $document): CodeCoverage
    {
        if ($xpath->query('filter/whitelist')->length !== 0) {
            return $this->legacyCodeCoverage($filename, $xpath, $document);
        }

        $cacheDirectory            = null;
        $pathCoverage              = false;
        $includeUncoveredFiles     = true;
        $processUncoveredFiles     = false;
        $ignoreDeprecatedCodeUnits = false;
        $disableCodeCoverageIgnore = false;

        $element = $this->element($xpath, 'coverage');

        if ($element) {
            $cacheDirectory = $this->getStringAttribute($element, 'cacheDirectory');

            if ($cacheDirectory !== null) {
                $cacheDirectory = new Directory(
                    $this->toAbsolutePath($filename, $cacheDirectory)
                );
            }

            $pathCoverage = $this->getBooleanAttribute(
                $element,
                'pathCoverage',
                false
            );

            $includeUncoveredFiles = $this->getBooleanAttribute(
                $element,
                'includeUncoveredFiles',
                true
            );

            $processUncoveredFiles = $this->getBooleanAttribute(
                $element,
                'processUncoveredFiles',
                false
            );

            $ignoreDeprecatedCodeUnits = $this->getBooleanAttribute(
                $element,
                'ignoreDeprecatedCodeUnits',
                false
            );

            $disableCodeCoverageIgnore = $this->getBooleanAttribute(
                $element,
                'disableCodeCoverageIgnore',
                false
            );
        }

        $clover  = null;
        $element = $this->element($xpath, 'coverage/report/clover');

        if ($element) {
            $clover = new Clover(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $cobertura = null;
        $element   = $this->element($xpath, 'coverage/report/cobertura');

        if ($element) {
            $cobertura = new Cobertura(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $crap4j  = null;
        $element = $this->element($xpath, 'coverage/report/crap4j');

        if ($element) {
            $crap4j = new Crap4j(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                ),
                $this->getIntegerAttribute($element, 'threshold', 30)
            );
        }

        $html    = null;
        $element = $this->element($xpath, 'coverage/report/html');

        if ($element) {
            $html = new CodeCoverageHtml(
                new Directory(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputDirectory')
                    )
                ),
                $this->getIntegerAttribute($element, 'lowUpperBound', 50),
                $this->getIntegerAttribute($element, 'highLowerBound', 90)
            );
        }

        $php     = null;
        $element = $this->element($xpath, 'coverage/report/php');

        if ($element) {
            $php = new CodeCoveragePhp(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                )
            );
        }

        $text    = null;
        $element = $this->element($xpath, 'coverage/report/text');

        if ($element) {
            $text = new CodeCoverageText(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputFile')
                    )
                ),
                $this->getBooleanAttribute($element, 'showUncoveredFiles', false),
                $this->getBooleanAttribute($element, 'showOnlySummary', false)
            );
        }

        $xml     = null;
        $element = $this->element($xpath, 'coverage/report/xml');

        if ($element) {
            $xml = new CodeCoverageXml(
                new Directory(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->getStringAttribute($element, 'outputDirectory')
                    )
                )
            );
        }

        return new CodeCoverage(
            $cacheDirectory,
            $this->readFilterDirectories($filename, $xpath, 'coverage/include/directory'),
            $this->readFilterFiles($filename, $xpath, 'coverage/include/file'),
            $this->readFilterDirectories($filename, $xpath, 'coverage/exclude/directory'),
            $this->readFilterFiles($filename, $xpath, 'coverage/exclude/file'),
            $pathCoverage,
            $includeUncoveredFiles,
            $processUncoveredFiles,
            $ignoreDeprecatedCodeUnits,
            $disableCodeCoverageIgnore,
            $clover,
            $cobertura,
            $crap4j,
            $html,
            $php,
            $text,
            $xml
        );
    }

    /**
     * @deprecated
     */
    private function legacyCodeCoverage(string $filename, DOMXPath $xpath, DOMDocument $document): CodeCoverage
    {
        $ignoreDeprecatedCodeUnits = $this->getBooleanAttribute(
            $document->documentElement,
            'ignoreDeprecatedCodeUnitsFromCodeCoverage',
            false
        );

        $disableCodeCoverageIgnore = $this->getBooleanAttribute(
            $document->documentElement,
            'disableCodeCoverageIgnore',
            false
        );

        $includeUncoveredFiles = true;
        $processUncoveredFiles = false;

        $element = $this->element($xpath, 'filter/whitelist');

        if ($element) {
            if ($element->hasAttribute('addUncoveredFilesFromWhitelist')) {
                $includeUncoveredFiles = (bool) $this->getBoolean(
                    (string) $element->getAttribute('addUncoveredFilesFromWhitelist'),
                    true
                );
            }

            if ($element->hasAttribute('processUncoveredFilesFromWhitelist')) {
                $processUncoveredFiles = (bool) $this->getBoolean(
                    (string) $element->getAttribute('processUncoveredFilesFromWhitelist'),
                    false
                );
            }
        }

        $clover    = null;
        $cobertura = null;
        $crap4j    = null;
        $html      = null;
        $php       = null;
        $text      = null;
        $xml       = null;

        foreach ($xpath->query('logging/log') as $log) {
            assert($log instanceof DOMElement);

            $type   = (string) $log->getAttribute('type');
            $target = (string) $log->getAttribute('target');

            if (!$target) {
                continue;
            }

            $target = $this->toAbsolutePath($filename, $target);

            switch ($type) {
                case 'coverage-clover':
                    $clover = new Clover(
                        new File($target)
                    );

                    break;

                case 'coverage-cobertura':
                    $cobertura = new Cobertura(
                        new File($target)
                    );

                    break;

                case 'coverage-crap4j':
                    $crap4j = new Crap4j(
                        new File($target),
                        $this->getIntegerAttribute($log, 'threshold', 30)
                    );

                    break;

                case 'coverage-html':
                    $html = new CodeCoverageHtml(
                        new Directory($target),
                        $this->getIntegerAttribute($log, 'lowUpperBound', 50),
                        $this->getIntegerAttribute($log, 'highLowerBound', 90)
                    );

                    break;

                case 'coverage-php':
                    $php = new CodeCoveragePhp(
                        new File($target)
                    );

                    break;

                case 'coverage-text':
                    $text = new CodeCoverageText(
                        new File($target),
                        $this->getBooleanAttribute($log, 'showUncoveredFiles', false),
                        $this->getBooleanAttribute($log, 'showOnlySummary', false)
                    );

                    break;

                case 'coverage-xml':
                    $xml = new CodeCoverageXml(
                        new Directory($target)
                    );

                    break;
            }
        }

        return new CodeCoverage(
            null,
            $this->readFilterDirectories($filename, $xpath, 'filter/whitelist/directory'),
            $this->readFilterFiles($filename, $xpath, 'filter/whitelist/file'),
            $this->readFilterDirectories($filename, $xpath, 'filter/whitelist/exclude/directory'),
            $this->readFilterFiles($filename, $xpath, 'filter/whitelist/exclude/file'),
            false,
            $includeUncoveredFiles,
            $processUncoveredFiles,
            $ignoreDeprecatedCodeUnits,
            $disableCodeCoverageIgnore,
            $clover,
            $cobertura,
            $crap4j,
            $html,
            $php,
            $text,
            $xml
        );
    }

    /**
     * If $value is 'false' or 'true', this returns the value that $value represents.
     * Otherwise, returns $default, which may be a string in rare cases.
     *
     * @see \PHPUnit\TextUI\XmlConfigurationTest::testPHPConfigurationIsReadCorrectly
     *
     * @param bool|string $default
     *
     * @return bool|string
     */
    private function getBoolean(string $value, $default)
    {
        if (strtolower($value) === 'false') {
            return false;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        return $default;
    }

    private function readFilterDirectories(string $filename, DOMXPath $xpath, string $query): FilterDirectoryCollection
    {
        $directories = [];

        foreach ($xpath->query($query) as $directoryNode) {
            assert($directoryNode instanceof DOMElement);

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

    private function readFilterFiles(string $filename, DOMXPath $xpath, string $query): FileCollection
    {
        $files = [];

        foreach ($xpath->query($query) as $file) {
            $filePath = (string) $file->textContent;

            if ($filePath) {
                $files[] = new File($this->toAbsolutePath($filename, $filePath));
            }
        }

        return FileCollection::fromArray($files);
    }

    private function groups(DOMXPath $xpath): Groups
    {
        return $this->parseGroupConfiguration($xpath, 'groups');
    }

    private function testdoxGroups(DOMXPath $xpath): Groups
    {
        return $this->parseGroupConfiguration($xpath, 'testdoxGroups');
    }

    private function parseGroupConfiguration(DOMXPath $xpath, string $root): Groups
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

    private function listeners(string $filename, DOMXPath $xpath): ExtensionCollection
    {
        $listeners = [];

        foreach ($xpath->query('listeners/listener') as $listener) {
            assert($listener instanceof DOMElement);

            $listeners[] = $this->getElementConfigurationParameters($filename, $listener);
        }

        return ExtensionCollection::fromArray($listeners);
    }

    private function getBooleanAttribute(DOMElement $element, string $attribute, bool $default): bool
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return (bool) $this->getBoolean(
            (string) $element->getAttribute($attribute),
            false
        );
    }

    private function getIntegerAttribute(DOMElement $element, string $attribute, int $default): int
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return $this->getInteger(
            (string) $element->getAttribute($attribute),
            $default
        );
    }

    private function getStringAttribute(DOMElement $element, string $attribute): ?string
    {
        if (!$element->hasAttribute($attribute)) {
            return null;
        }

        return (string) $element->getAttribute($attribute);
    }

    private function getInteger(string $value, int $default): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function php(string $filename, DOMXPath $xpath): Php
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
            assert($ini instanceof DOMElement);

            $iniSettings[] = new IniSetting(
                (string) $ini->getAttribute('name'),
                (string) $ini->getAttribute('value')
            );
        }

        $constants = [];

        foreach ($xpath->query('php/const') as $const) {
            assert($const instanceof DOMElement);

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
                assert($var instanceof DOMElement);

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

    private function phpunit(string $filename, DOMDocument $document): PHPUnit
    {
        $executionOrder      = TestSuiteSorter::ORDER_DEFAULT;
        $defectsFirst        = false;
        $resolveDependencies = $this->getBooleanAttribute($document->documentElement, 'resolveDependencies', true);

        if ($document->documentElement->hasAttribute('executionOrder')) {
            foreach (explode(',', $document->documentElement->getAttribute('executionOrder')) as $order) {
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
            $this->getBooleanAttribute($document->documentElement, 'cacheResult', true),
            $cacheResultFile,
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
            $bootstrap,
            $this->getBooleanAttribute($document->documentElement, 'processIsolation', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnEmptyTestSuite', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnIncomplete', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnRisky', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnSkipped', false),
            $this->getBooleanAttribute($document->documentElement, 'failOnWarning', false),
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

    private function getColors(DOMDocument $document): string
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
    private function getColumns(DOMDocument $document)
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

    private function testSuite(string $filename, DOMXPath $xpath): TestSuiteCollection
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
                assert($directoryNode instanceof DOMElement);

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

                $phpVersion = PHP_VERSION;

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
                assert($fileNode instanceof DOMElement);

                $file = (string) $fileNode->textContent;

                if (empty($file)) {
                    continue;
                }

                $phpVersion = PHP_VERSION;

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
     * @return DOMElement[]
     */
    private function getTestSuiteElements(DOMXPath $xpath): array
    {
        /** @var DOMElement[] $elements */
        $elements = [];

        $testSuiteNodes = $xpath->query('testsuites/testsuite');

        if ($testSuiteNodes->length === 0) {
            $testSuiteNodes = $xpath->query('testsuite');
        }

        if ($testSuiteNodes->length === 1) {
            $element = $testSuiteNodes->item(0);

            assert($element instanceof DOMElement);

            $elements[] = $element;
        } else {
            foreach ($testSuiteNodes as $testSuiteNode) {
                assert($testSuiteNode instanceof DOMElement);

                $elements[] = $testSuiteNode;
            }
        }

        return $elements;
    }

    private function element(DOMXPath $xpath, string $element): ?DOMElement
    {
        $nodes = $xpath->query($element);

        if ($nodes->length === 1) {
            $node = $nodes->item(0);

            assert($node instanceof DOMElement);

            return $node;
        }

        return null;
    }
}
