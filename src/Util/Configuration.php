<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Configuration
{
    /**
     * @var self[]
     */
    private static $instances = [];

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @var DOMXPath
     */
    private $xpath;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var \LibXMLError[]
     */
    private $errors = [];

    /**
     * Returns a PHPUnit configuration object.
     *
     * @throws Exception
     */
    public static function getInstance(string $filename): self
    {
        $realPath = \realpath($filename);

        if ($realPath === false) {
            throw new Exception(
                \sprintf(
                    'Could not read "%s".',
                    $filename
                )
            );
        }

        if (!isset(self::$instances[$realPath])) {
            self::$instances[$realPath] = new self($realPath);
        }

        return self::$instances[$realPath];
    }

    /**
     * Loads a PHPUnit configuration file.
     *
     * @throws Exception
     */
    private function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->document = Xml::loadFile($filename, false, true, true);
        $this->xpath    = new DOMXPath($this->document);

        $this->validateConfigurationAgainstSchema();
    }

    /**
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    public function hasValidationErrors(): bool
    {
        return \count($this->errors) > 0;
    }

    public function getValidationErrors(): array
    {
        $result = [];

        foreach ($this->errors as $error) {
            if (!isset($result[$error->line])) {
                $result[$error->line] = [];
            }
            $result[$error->line][] = \trim($error->message);
        }

        return $result;
    }

    /**
     * Returns the real path to the configuration file.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getExtensionConfiguration(): array
    {
        $result = [];

        foreach ($this->xpath->query('extensions/extension') as $extension) {
            \assert($extension instanceof DOMElement);

            $class     = (string) $extension->getAttribute('class');
            $file      = '';
            $arguments = $this->getConfigurationArguments($extension->childNodes);

            if ($extension->getAttribute('file')) {
                $file = $this->toAbsolutePath(
                    (string) $extension->getAttribute('file'),
                    true
                );
            }
            $result[] = [
                'class'     => $class,
                'file'      => $file,
                'arguments' => $arguments,
            ];
        }

        return $result;
    }

    /**
     * Returns the configuration for SUT filtering.
     */
    public function getFilterConfiguration(): array
    {
        $addUncoveredFilesFromWhitelist     = true;
        $processUncoveredFilesFromWhitelist = false;
        $includeDirectory                   = [];
        $includeFile                        = [];
        $excludeDirectory                   = [];
        $excludeFile                        = [];

        $tmp = $this->xpath->query('filter/whitelist');

        if ($tmp->length === 1) {
            if ($tmp->item(0)->hasAttribute('addUncoveredFilesFromWhitelist')) {
                $addUncoveredFilesFromWhitelist = $this->getBoolean(
                    (string) $tmp->item(0)->getAttribute(
                        'addUncoveredFilesFromWhitelist'
                    ),
                    true
                );
            }

            if ($tmp->item(0)->hasAttribute('processUncoveredFilesFromWhitelist')) {
                $processUncoveredFilesFromWhitelist = $this->getBoolean(
                    (string) $tmp->item(0)->getAttribute(
                        'processUncoveredFilesFromWhitelist'
                    ),
                    false
                );
            }

            $includeDirectory = $this->readFilterDirectories(
                'filter/whitelist/directory'
            );

            $includeFile = $this->readFilterFiles(
                'filter/whitelist/file'
            );

            $excludeDirectory = $this->readFilterDirectories(
                'filter/whitelist/exclude/directory'
            );

            $excludeFile = $this->readFilterFiles(
                'filter/whitelist/exclude/file'
            );
        }

        return [
            'whitelist' => [
                'addUncoveredFilesFromWhitelist'     => $addUncoveredFilesFromWhitelist,
                'processUncoveredFilesFromWhitelist' => $processUncoveredFilesFromWhitelist,
                'include'                            => [
                    'directory' => $includeDirectory,
                    'file'      => $includeFile,
                ],
                'exclude' => [
                    'directory' => $excludeDirectory,
                    'file'      => $excludeFile,
                ],
            ],
        ];
    }

    /**
     * Returns the configuration for groups.
     */
    public function getGroupConfiguration(): array
    {
        return $this->parseGroupConfiguration('groups');
    }

    /**
     * Returns the configuration for testdox groups.
     */
    public function getTestdoxGroupConfiguration(): array
    {
        return $this->parseGroupConfiguration('testdoxGroups');
    }

    /**
     * Returns the configuration for listeners.
     */
    public function getListenerConfiguration(): array
    {
        $result = [];

        foreach ($this->xpath->query('listeners/listener') as $listener) {
            \assert($listener instanceof DOMElement);

            $class     = (string) $listener->getAttribute('class');
            $file      = '';
            $arguments = $this->getConfigurationArguments($listener->childNodes);

            if ($listener->getAttribute('file')) {
                $file = $this->toAbsolutePath(
                    (string) $listener->getAttribute('file'),
                    true
                );
            }

            $result[] = [
                'class'     => $class,
                'file'      => $file,
                'arguments' => $arguments,
            ];
        }

        return $result;
    }

    /**
     * Returns the logging configuration.
     */
    public function getLoggingConfiguration(): array
    {
        $result = [];

        foreach ($this->xpath->query('logging/log') as $log) {
            \assert($log instanceof DOMElement);

            $type   = (string) $log->getAttribute('type');
            $target = (string) $log->getAttribute('target');

            if (!$target) {
                continue;
            }

            $target = $this->toAbsolutePath($target);

            if ($type === 'coverage-html') {
                if ($log->hasAttribute('lowUpperBound')) {
                    $result['lowUpperBound'] = $this->getInteger(
                        (string) $log->getAttribute('lowUpperBound'),
                        50
                    );
                }

                if ($log->hasAttribute('highLowerBound')) {
                    $result['highLowerBound'] = $this->getInteger(
                        (string) $log->getAttribute('highLowerBound'),
                        90
                    );
                }
            } elseif ($type === 'coverage-crap4j') {
                if ($log->hasAttribute('threshold')) {
                    $result['crap4jThreshold'] = $this->getInteger(
                        (string) $log->getAttribute('threshold'),
                        30
                    );
                }
            } elseif ($type === 'coverage-text') {
                if ($log->hasAttribute('showUncoveredFiles')) {
                    $result['coverageTextShowUncoveredFiles'] = $this->getBoolean(
                        (string) $log->getAttribute('showUncoveredFiles'),
                        false
                    );
                }

                if ($log->hasAttribute('showOnlySummary')) {
                    $result['coverageTextShowOnlySummary'] = $this->getBoolean(
                        (string) $log->getAttribute('showOnlySummary'),
                        false
                    );
                }
            }

            $result[$type] = $target;
        }

        return $result;
    }

    /**
     * Returns the PHP configuration.
     */
    public function getPHPConfiguration(): array
    {
        $result = [
            'include_path' => [],
            'ini'          => [],
            'const'        => [],
            'var'          => [],
            'env'          => [],
            'post'         => [],
            'get'          => [],
            'cookie'       => [],
            'server'       => [],
            'files'        => [],
            'request'      => [],
        ];

        foreach ($this->xpath->query('php/includePath') as $includePath) {
            $path = (string) $includePath->textContent;

            if ($path) {
                $result['include_path'][] = $this->toAbsolutePath($path);
            }
        }

        foreach ($this->xpath->query('php/ini') as $ini) {
            \assert($ini instanceof DOMElement);

            $name  = (string) $ini->getAttribute('name');
            $value = (string) $ini->getAttribute('value');

            $result['ini'][$name]['value'] = $value;
        }

        foreach ($this->xpath->query('php/const') as $const) {
            \assert($const instanceof  DOMElement);

            $name  = (string) $const->getAttribute('name');
            $value = (string) $const->getAttribute('value');

            $result['const'][$name]['value'] = $this->getBoolean($value, $value);
        }

        foreach (['var', 'env', 'post', 'get', 'cookie', 'server', 'files', 'request'] as $array) {
            foreach ($this->xpath->query('php/' . $array) as $var) {
                \assert($var instanceof DOMElement);

                $name     = (string) $var->getAttribute('name');
                $value    = (string) $var->getAttribute('value');
                $verbatim = false;

                if ($var->hasAttribute('verbatim')) {
                    $verbatim                          = $this->getBoolean($var->getAttribute('verbatim'), false);
                    $result[$array][$name]['verbatim'] = $verbatim;
                }

                if ($var->hasAttribute('force')) {
                    $force                          = $this->getBoolean($var->getAttribute('force'), false);
                    $result[$array][$name]['force'] = $force;
                }

                if (!$verbatim) {
                    $value = $this->getBoolean($value, $value);
                }

                $result[$array][$name]['value'] = $value;
            }
        }

        return $result;
    }

    /**
     * Handles the PHP configuration.
     */
    public function handlePHPConfiguration(): void
    {
        $configuration = $this->getPHPConfiguration();

        if (!empty($configuration['include_path'])) {
            \ini_set(
                'include_path',
                \implode(\PATH_SEPARATOR, $configuration['include_path']) .
                \PATH_SEPARATOR .
                \ini_get('include_path')
            );
        }

        foreach ($configuration['ini'] as $name => $data) {
            $value = $data['value'];

            if (\defined($value)) {
                $value = (string) \constant($value);
            }

            \ini_set($name, $value);
        }

        foreach ($configuration['const'] as $name => $data) {
            $value = $data['value'];

            if (!\defined($name)) {
                \define($name, $value);
            }
        }

        foreach (['var', 'post', 'get', 'cookie', 'server', 'files', 'request'] as $array) {
            /*
             * @see https://github.com/sebastianbergmann/phpunit/issues/277
             */
            switch ($array) {
                case 'var':
                    $target = &$GLOBALS;

                    break;

                case 'server':
                    $target = &$_SERVER;

                    break;

                default:
                    $target = &$GLOBALS['_' . \strtoupper($array)];

                    break;
            }

            foreach ($configuration[$array] as $name => $data) {
                $target[$name] = $data['value'];
            }
        }

        foreach ($configuration['env'] as $name => $data) {
            $value = $data['value'];
            $force = $data['force'] ?? false;

            if ($force || \getenv($name) === false) {
                \putenv("{$name}={$value}");
            }

            $value = \getenv($name);

            if (!isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }

            if ($force) {
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Returns the PHPUnit configuration.
     */
    public function getPHPUnitConfiguration(): array
    {
        $result = [];
        $root   = $this->document->documentElement;

        if ($root->hasAttribute('cacheTokens')) {
            $result['cacheTokens'] = $this->getBoolean(
                (string) $root->getAttribute('cacheTokens'),
                false
            );
        }

        if ($root->hasAttribute('columns')) {
            $columns = (string) $root->getAttribute('columns');

            if ($columns === 'max') {
                $result['columns'] = 'max';
            } else {
                $result['columns'] = $this->getInteger($columns, 80);
            }
        }

        if ($root->hasAttribute('colors')) {
            /* only allow boolean for compatibility with previous versions
              'always' only allowed from command line */
            if ($this->getBoolean($root->getAttribute('colors'), false)) {
                $result['colors'] = ResultPrinter::COLOR_AUTO;
            } else {
                $result['colors'] = ResultPrinter::COLOR_NEVER;
            }
        }

        /*
         * @see https://github.com/sebastianbergmann/phpunit/issues/657
         */
        if ($root->hasAttribute('stderr')) {
            $result['stderr'] = $this->getBoolean(
                (string) $root->getAttribute('stderr'),
                false
            );
        }

        if ($root->hasAttribute('backupGlobals')) {
            $result['backupGlobals'] = $this->getBoolean(
                (string) $root->getAttribute('backupGlobals'),
                false
            );
        }

        if ($root->hasAttribute('backupStaticAttributes')) {
            $result['backupStaticAttributes'] = $this->getBoolean(
                (string) $root->getAttribute('backupStaticAttributes'),
                false
            );
        }

        if ($root->getAttribute('bootstrap')) {
            $result['bootstrap'] = $this->toAbsolutePath(
                (string) $root->getAttribute('bootstrap')
            );
        }

        if ($root->hasAttribute('convertDeprecationsToExceptions')) {
            $result['convertDeprecationsToExceptions'] = $this->getBoolean(
                (string) $root->getAttribute('convertDeprecationsToExceptions'),
                true
            );
        }

        if ($root->hasAttribute('convertErrorsToExceptions')) {
            $result['convertErrorsToExceptions'] = $this->getBoolean(
                (string) $root->getAttribute('convertErrorsToExceptions'),
                true
            );
        }

        if ($root->hasAttribute('convertNoticesToExceptions')) {
            $result['convertNoticesToExceptions'] = $this->getBoolean(
                (string) $root->getAttribute('convertNoticesToExceptions'),
                true
            );
        }

        if ($root->hasAttribute('convertWarningsToExceptions')) {
            $result['convertWarningsToExceptions'] = $this->getBoolean(
                (string) $root->getAttribute('convertWarningsToExceptions'),
                true
            );
        }

        if ($root->hasAttribute('forceCoversAnnotation')) {
            $result['forceCoversAnnotation'] = $this->getBoolean(
                (string) $root->getAttribute('forceCoversAnnotation'),
                false
            );
        }

        if ($root->hasAttribute('disableCodeCoverageIgnore')) {
            $result['disableCodeCoverageIgnore'] = $this->getBoolean(
                (string) $root->getAttribute('disableCodeCoverageIgnore'),
                false
            );
        }

        if ($root->hasAttribute('processIsolation')) {
            $result['processIsolation'] = $this->getBoolean(
                (string) $root->getAttribute('processIsolation'),
                false
            );
        }

        if ($root->hasAttribute('stopOnDefect')) {
            $result['stopOnDefect'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnDefect'),
                false
            );
        }

        if ($root->hasAttribute('stopOnError')) {
            $result['stopOnError'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnError'),
                false
            );
        }

        if ($root->hasAttribute('stopOnFailure')) {
            $result['stopOnFailure'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnFailure'),
                false
            );
        }

        if ($root->hasAttribute('stopOnWarning')) {
            $result['stopOnWarning'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnWarning'),
                false
            );
        }

        if ($root->hasAttribute('stopOnIncomplete')) {
            $result['stopOnIncomplete'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnIncomplete'),
                false
            );
        }

        if ($root->hasAttribute('stopOnRisky')) {
            $result['stopOnRisky'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnRisky'),
                false
            );
        }

        if ($root->hasAttribute('stopOnSkipped')) {
            $result['stopOnSkipped'] = $this->getBoolean(
                (string) $root->getAttribute('stopOnSkipped'),
                false
            );
        }

        if ($root->hasAttribute('failOnWarning')) {
            $result['failOnWarning'] = $this->getBoolean(
                (string) $root->getAttribute('failOnWarning'),
                false
            );
        }

        if ($root->hasAttribute('failOnRisky')) {
            $result['failOnRisky'] = $this->getBoolean(
                (string) $root->getAttribute('failOnRisky'),
                false
            );
        }

        if ($root->hasAttribute('testSuiteLoaderClass')) {
            $result['testSuiteLoaderClass'] = (string) $root->getAttribute(
                'testSuiteLoaderClass'
            );
        }

        if ($root->hasAttribute('defaultTestSuite')) {
            $result['defaultTestSuite'] = (string) $root->getAttribute(
                'defaultTestSuite'
            );
        }

        if ($root->getAttribute('testSuiteLoaderFile')) {
            $result['testSuiteLoaderFile'] = $this->toAbsolutePath(
                (string) $root->getAttribute('testSuiteLoaderFile')
            );
        }

        if ($root->hasAttribute('printerClass')) {
            $result['printerClass'] = (string) $root->getAttribute(
                'printerClass'
            );
        }

        if ($root->getAttribute('printerFile')) {
            $result['printerFile'] = $this->toAbsolutePath(
                (string) $root->getAttribute('printerFile')
            );
        }

        if ($root->hasAttribute('beStrictAboutChangesToGlobalState')) {
            $result['beStrictAboutChangesToGlobalState'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutChangesToGlobalState'),
                false
            );
        }

        if ($root->hasAttribute('beStrictAboutOutputDuringTests')) {
            $result['disallowTestOutput'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutOutputDuringTests'),
                false
            );
        }

        if ($root->hasAttribute('beStrictAboutResourceUsageDuringSmallTests')) {
            $result['beStrictAboutResourceUsageDuringSmallTests'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutResourceUsageDuringSmallTests'),
                false
            );
        }

        if ($root->hasAttribute('beStrictAboutTestsThatDoNotTestAnything')) {
            $result['reportUselessTests'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutTestsThatDoNotTestAnything'),
                true
            );
        }

        if ($root->hasAttribute('beStrictAboutTodoAnnotatedTests')) {
            $result['disallowTodoAnnotatedTests'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutTodoAnnotatedTests'),
                false
            );
        }

        if ($root->hasAttribute('beStrictAboutCoversAnnotation')) {
            $result['strictCoverage'] = $this->getBoolean(
                (string) $root->getAttribute('beStrictAboutCoversAnnotation'),
                false
            );
        }

        if ($root->hasAttribute('defaultTimeLimit')) {
            $result['defaultTimeLimit'] = $this->getInteger(
                (string) $root->getAttribute('defaultTimeLimit'),
                1
            );
        }

        if ($root->hasAttribute('enforceTimeLimit')) {
            $result['enforceTimeLimit'] = $this->getBoolean(
                (string) $root->getAttribute('enforceTimeLimit'),
                false
            );
        }

        if ($root->hasAttribute('ignoreDeprecatedCodeUnitsFromCodeCoverage')) {
            $result['ignoreDeprecatedCodeUnitsFromCodeCoverage'] = $this->getBoolean(
                (string) $root->getAttribute('ignoreDeprecatedCodeUnitsFromCodeCoverage'),
                false
            );
        }

        if ($root->hasAttribute('timeoutForSmallTests')) {
            $result['timeoutForSmallTests'] = $this->getInteger(
                (string) $root->getAttribute('timeoutForSmallTests'),
                1
            );
        }

        if ($root->hasAttribute('timeoutForMediumTests')) {
            $result['timeoutForMediumTests'] = $this->getInteger(
                (string) $root->getAttribute('timeoutForMediumTests'),
                10
            );
        }

        if ($root->hasAttribute('timeoutForLargeTests')) {
            $result['timeoutForLargeTests'] = $this->getInteger(
                (string) $root->getAttribute('timeoutForLargeTests'),
                60
            );
        }

        if ($root->hasAttribute('reverseDefectList')) {
            $result['reverseDefectList'] = $this->getBoolean(
                (string) $root->getAttribute('reverseDefectList'),
                false
            );
        }

        if ($root->hasAttribute('verbose')) {
            $result['verbose'] = $this->getBoolean(
                (string) $root->getAttribute('verbose'),
                false
            );
        }

        if ($root->hasAttribute('testdox')) {
            $testdox = $this->getBoolean(
                (string) $root->getAttribute('testdox'),
                false
            );

            if ($testdox) {
                if (isset($result['printerClass'])) {
                    $result['conflictBetweenPrinterClassAndTestdox'] = true;
                } else {
                    $result['printerClass'] = CliTestDoxPrinter::class;
                }
            }
        }

        if ($root->hasAttribute('registerMockObjectsFromTestArgumentsRecursively')) {
            $result['registerMockObjectsFromTestArgumentsRecursively'] = $this->getBoolean(
                (string) $root->getAttribute('registerMockObjectsFromTestArgumentsRecursively'),
                false
            );
        }

        if ($root->hasAttribute('extensionsDirectory')) {
            $result['extensionsDirectory'] = $this->toAbsolutePath(
                (string) $root->getAttribute(
                    'extensionsDirectory'
                )
            );
        }

        if ($root->hasAttribute('cacheResult')) {
            $result['cacheResult'] = $this->getBoolean(
                (string) $root->getAttribute('cacheResult'),
                true
            );
        }

        if ($root->hasAttribute('cacheResultFile')) {
            $result['cacheResultFile'] = $this->toAbsolutePath(
                (string) $root->getAttribute('cacheResultFile')
            );
        }

        if ($root->hasAttribute('executionOrder')) {
            foreach (\explode(',', $root->getAttribute('executionOrder')) as $order) {
                switch ($order) {
                    case 'default':
                        $result['executionOrder']        = TestSuiteSorter::ORDER_DEFAULT;
                        $result['executionOrderDefects'] = TestSuiteSorter::ORDER_DEFAULT;
                        $result['resolveDependencies']   = false;

                        break;
                    case 'reverse':
                        $result['executionOrder'] = TestSuiteSorter::ORDER_REVERSED;

                        break;
                    case 'random':
                        $result['executionOrder'] = TestSuiteSorter::ORDER_RANDOMIZED;

                        break;
                    case 'defects':
                        $result['executionOrderDefects'] = TestSuiteSorter::ORDER_DEFECTS_FIRST;

                        break;
                    case 'depends':
                        $result['resolveDependencies'] = true;

                        break;
                    case 'no-depends':
                        $result['resolveDependencies'] = false;

                        break;
                }
            }
        }

        if ($root->hasAttribute('resolveDependencies')) {
            $result['resolveDependencies'] = $this->getBoolean(
                (string) $root->getAttribute('resolveDependencies'),
                false
            );
        }

        if ($root->hasAttribute('noInteraction')) {
            $result['noInteraction'] = $this->getBoolean(
                (string) $root->getAttribute('noInteraction'),
                false
            );
        }

        return $result;
    }

    /**
     * Returns the test suite configuration.
     *
     * @throws Exception
     * @throws \ReflectionException
     */
    public function getTestSuiteConfiguration(string $testSuiteFilter = ''): TestSuite
    {
        $testSuiteNodes = $this->xpath->query('testsuites/testsuite');

        if ($testSuiteNodes->length === 0) {
            $testSuiteNodes = $this->xpath->query('testsuite');
        }

        if ($testSuiteNodes->length === 1) {
            return $this->getTestSuite($testSuiteNodes->item(0), $testSuiteFilter);
        }

        $suite = new TestSuite;

        foreach ($testSuiteNodes as $testSuiteNode) {
            $suite->addTestSuite(
                $this->getTestSuite($testSuiteNode, $testSuiteFilter)
            );
        }

        return $suite;
    }

    /**
     * Returns the test suite names from the configuration.
     */
    public function getTestSuiteNames(): array
    {
        $names = [];

        foreach ($this->xpath->query('*/testsuite') as $node) {
            /* @var DOMElement $node */
            $names[] = $node->getAttribute('name');
        }

        return $names;
    }

    private function validateConfigurationAgainstSchema(): void
    {
        $original    = \libxml_use_internal_errors(true);
        $xsdFilename = __DIR__ . '/../../phpunit.xsd';

        if (\defined('__PHPUNIT_PHAR_ROOT__')) {
            $xsdFilename =  __PHPUNIT_PHAR_ROOT__ . '/phpunit.xsd';
        }

        $this->document->schemaValidate($xsdFilename);
        $this->errors = \libxml_get_errors();
        \libxml_clear_errors();
        \libxml_use_internal_errors($original);
    }

    /**
     * Collects and returns the configuration arguments from the PHPUnit
     * XML configuration
     */
    private function getConfigurationArguments(\DOMNodeList $nodes): array
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
                    $arguments[] = $this->toAbsolutePath((string) $argument->textContent);
                } else {
                    $arguments[] = Xml::xmlToVariable($argument);
                }
            }
        }

        return $arguments;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    private function getTestSuite(DOMElement $testSuiteNode, string $testSuiteFilter = ''): TestSuite
    {
        if ($testSuiteNode->hasAttribute('name')) {
            $suite = new TestSuite(
                (string) $testSuiteNode->getAttribute('name')
            );
        } else {
            $suite = new TestSuite;
        }

        $exclude = [];

        foreach ($testSuiteNode->getElementsByTagName('exclude') as $excludeNode) {
            $excludeFile = (string) $excludeNode->textContent;

            if ($excludeFile) {
                $exclude[] = $this->toAbsolutePath($excludeFile);
            }
        }

        $fileIteratorFacade = new FileIteratorFacade;
        $testSuiteFilter    = $testSuiteFilter ? \explode(',', $testSuiteFilter) : [];

        foreach ($testSuiteNode->getElementsByTagName('directory') as $directoryNode) {
            \assert($directoryNode instanceof DOMElement);

            if (!empty($testSuiteFilter) && !\in_array($directoryNode->parentNode->getAttribute('name'), $testSuiteFilter)) {
                continue;
            }

            $directory = (string) $directoryNode->textContent;

            if (empty($directory)) {
                continue;
            }

            $prefix = '';
            $suffix = 'Test.php';

            if (!$this->satisfiesPhpVersion($directoryNode)) {
                continue;
            }

            if ($directoryNode->hasAttribute('prefix')) {
                $prefix = (string) $directoryNode->getAttribute('prefix');
            }

            if ($directoryNode->hasAttribute('suffix')) {
                $suffix = (string) $directoryNode->getAttribute('suffix');
            }

            $files = $fileIteratorFacade->getFilesAsArray(
                $this->toAbsolutePath($directory),
                $suffix,
                $prefix,
                $exclude
            );

            $suite->addTestFiles($files);
        }

        foreach ($testSuiteNode->getElementsByTagName('file') as $fileNode) {
            \assert($fileNode instanceof DOMElement);

            if (!empty($testSuiteFilter) && !\in_array($fileNode->parentNode->getAttribute('name'), $testSuiteFilter)) {
                continue;
            }

            $file = (string) $fileNode->textContent;

            if (empty($file)) {
                continue;
            }

            $file = $fileIteratorFacade->getFilesAsArray(
                $this->toAbsolutePath($file)
            );

            if (!isset($file[0])) {
                continue;
            }

            $file = $file[0];

            if (!$this->satisfiesPhpVersion($fileNode)) {
                continue;
            }

            $suite->addTestFile($file);
        }

        return $suite;
    }

    private function satisfiesPhpVersion(DOMElement $node): bool
    {
        $phpVersion         = \PHP_VERSION;
        $phpVersionOperator = '>=';

        if ($node->hasAttribute('phpVersion')) {
            $phpVersion = (string) $node->getAttribute('phpVersion');
        }

        if ($node->hasAttribute('phpVersionOperator')) {
            $phpVersionOperator = (string) $node->getAttribute('phpVersionOperator');
        }

        return \version_compare(\PHP_VERSION, $phpVersion, $phpVersionOperator);
    }

    /**
     * if $value is 'false' or 'true', this returns the value that $value represents.
     * Otherwise, returns $default, which may be a string in rare cases.
     * See PHPUnit\Util\ConfigurationTest::testPHPConfigurationIsReadCorrectly
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

    private function getInteger(string $value, int $default): int
    {
        if (\is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function readFilterDirectories(string $query): array
    {
        $directories = [];

        foreach ($this->xpath->query($query) as $directoryNode) {
            \assert($directoryNode instanceof DOMElement);

            $directoryPath = (string) $directoryNode->textContent;

            if (!$directoryPath) {
                continue;
            }

            $prefix = '';
            $suffix = '.php';
            $group  = 'DEFAULT';

            if ($directoryNode->hasAttribute('prefix')) {
                $prefix = (string) $directoryNode->getAttribute('prefix');
            }

            if ($directoryNode->hasAttribute('suffix')) {
                $suffix = (string) $directoryNode->getAttribute('suffix');
            }

            if ($directoryNode->hasAttribute('group')) {
                $group = (string) $directoryNode->getAttribute('group');
            }

            $directories[] = [
                'path'   => $this->toAbsolutePath($directoryPath),
                'prefix' => $prefix,
                'suffix' => $suffix,
                'group'  => $group,
            ];
        }

        return $directories;
    }

    /**
     * @return string[]
     */
    private function readFilterFiles(string $query): array
    {
        $files = [];

        foreach ($this->xpath->query($query) as $file) {
            $filePath = (string) $file->textContent;

            if ($filePath) {
                $files[] = $this->toAbsolutePath($filePath);
            }
        }

        return $files;
    }

    private function toAbsolutePath(string $path, bool $useIncludePath = false): string
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

        $file = \dirname($this->filename) . \DIRECTORY_SEPARATOR . $path;

        if ($useIncludePath && !\file_exists($file)) {
            $includePathFile = \stream_resolve_include_path($path);

            if ($includePathFile) {
                $file = $includePathFile;
            }
        }

        return $file;
    }

    private function parseGroupConfiguration(string $root): array
    {
        $groups = [
            'include' => [],
            'exclude' => [],
        ];

        foreach ($this->xpath->query($root . '/include/group') as $group) {
            $groups['include'][] = (string) $group->textContent;
        }

        foreach ($this->xpath->query($root . '/exclude/group') as $group) {
            $groups['exclude'][] = (string) $group->textContent;
        }

        return $groups;
    }
}
