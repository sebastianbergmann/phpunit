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
use const PHP_EOL;
use const PHP_VERSION;
use function assert;
use function defined;
use function dirname;
use function explode;
use function is_numeric;
use function max;
use function preg_match;
use function realpath;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
use function trim;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Constant;
use PHPUnit\TextUI\Configuration\ConstantCollection;
use PHPUnit\TextUI\Configuration\Directory;
use PHPUnit\TextUI\Configuration\DirectoryCollection;
use PHPUnit\TextUI\Configuration\ExtensionBootstrap;
use PHPUnit\TextUI\Configuration\ExtensionBootstrapCollection;
use PHPUnit\TextUI\Configuration\File;
use PHPUnit\TextUI\Configuration\FileCollection;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Group;
use PHPUnit\TextUI\Configuration\GroupCollection;
use PHPUnit\TextUI\Configuration\IniSetting;
use PHPUnit\TextUI\Configuration\IniSettingCollection;
use PHPUnit\TextUI\Configuration\Php;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\Configuration\TestDirectory;
use PHPUnit\TextUI\Configuration\TestDirectoryCollection;
use PHPUnit\TextUI\Configuration\TestFile;
use PHPUnit\TextUI\Configuration\TestFileCollection;
use PHPUnit\TextUI\Configuration\TestSuite as TestSuiteConfiguration;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\Configuration\Variable;
use PHPUnit\TextUI\Configuration\VariableCollection;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Clover;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Cobertura;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Crap4j;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Html as CodeCoverageHtml;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\OpenClover;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php as CodeCoveragePhp;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Text as CodeCoverageText;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Xml as CodeCoverageXml;
use PHPUnit\TextUI\XmlConfiguration\Logging\Junit;
use PHPUnit\TextUI\XmlConfiguration\Logging\Logging;
use PHPUnit\TextUI\XmlConfiguration\Logging\Otr;
use PHPUnit\TextUI\XmlConfiguration\Logging\TeamCity;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Html as TestDoxHtml;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Text as TestDoxText;
use PHPUnit\Util\VersionComparisonOperator;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Loader
{
    /**
     * @throws Exception
     */
    public function load(string $filename): LoadedFromFileConfiguration
    {
        try {
            $document = (new XmlLoader)->loadFile($filename);
        } catch (XmlException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $xpath = new DOMXPath($document);

        try {
            $xsdFilename = (new SchemaFinder)->find(Version::series());
        } catch (CannotFindSchemaException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $configurationFileRealpath = realpath($filename);

        assert($configurationFileRealpath !== false && $configurationFileRealpath !== '');

        $validationResult = (new Validator)->validate($document, $xsdFilename);

        if ($validationResult->hasValidationErrors()) {
            $this->ensureConfigurationValidatesAgainstAtLeastOneSchema(
                $document,
                $configurationFileRealpath,
                $validationResult,
            );
        }

        try {
            return new LoadedFromFileConfiguration(
                $configurationFileRealpath,
                $validationResult,
                $this->extensions($xpath),
                $this->source($configurationFileRealpath, $xpath),
                $this->codeCoverage($configurationFileRealpath, $xpath),
                $this->groups($xpath),
                $this->logging($configurationFileRealpath, $xpath),
                $this->php($configurationFileRealpath, $xpath),
                $this->phpunit($configurationFileRealpath, $document, $xpath),
                $this->testSuite($configurationFileRealpath, $xpath),
            );
        } catch (Throwable $t) {
            $message = sprintf(
                'Cannot load XML configuration file %s',
                $configurationFileRealpath,
            );

            if ($validationResult->hasValidationErrors()) {
                $message .= ' because it has validation errors:' . PHP_EOL . $validationResult->asString();
            }

            throw new Exception($message, previous: $t);
        }
    }

    private function logging(string $filename, DOMXPath $xpath): Logging
    {
        $junit   = null;
        $element = $this->element($xpath, 'logging/junit');

        if ($element !== null) {
            $junit = new Junit(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $otr     = null;
        $element = $this->element($xpath, 'logging/otr');

        if ($element !== null) {
            $otr = new Otr(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
                $this->parseBooleanAttribute($element, 'includeGitInformation', false),
            );
        }

        $teamCity = null;
        $element  = $this->element($xpath, 'logging/teamcity');

        if ($element !== null) {
            $teamCity = new TeamCity(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $testDoxHtml = null;
        $element     = $this->element($xpath, 'logging/testdoxHtml');

        if ($element !== null) {
            $testDoxHtml = new TestDoxHtml(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $testDoxText = null;
        $element     = $this->element($xpath, 'logging/testdoxText');

        if ($element !== null) {
            $testDoxText = new TestDoxText(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        return new Logging(
            $junit,
            $otr,
            $teamCity,
            $testDoxHtml,
            $testDoxText,
        );
    }

    private function extensions(DOMXPath $xpath): ExtensionBootstrapCollection
    {
        $extensionBootstrappers = [];

        $bootstrapNodes = $xpath->query('extensions/bootstrap');

        assert($bootstrapNodes instanceof DOMNodeList);

        foreach ($bootstrapNodes as $bootstrap) {
            assert($bootstrap instanceof DOMElement);

            $parameters = [];

            $parameterNodes = $xpath->query('parameter', $bootstrap);

            assert($parameterNodes instanceof DOMNodeList);

            foreach ($parameterNodes as $parameter) {
                assert($parameter instanceof DOMElement);

                $parameters[$parameter->getAttribute('name')] = $parameter->getAttribute('value');
            }

            $className = $bootstrap->getAttribute('class');

            assert($className !== '');

            $extensionBootstrappers[] = new ExtensionBootstrap(
                $className,
                $parameters,
            );
        }

        return ExtensionBootstrapCollection::fromArray($extensionBootstrappers);
    }

    /**
     * @return non-empty-string
     */
    private function toAbsolutePath(string $filename, string $path): string
    {
        $path = trim($path);

        if (str_starts_with($path, '/')) {
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
            $path !== '' &&
            ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]:[/\\\]#i', substr($path, 0, 3)) === 1))) {
            return $path;
        }

        if (str_contains($path, '://')) {
            return $path;
        }

        return dirname($filename) . DIRECTORY_SEPARATOR . $path;
    }

    private function source(string $filename, DOMXPath $xpath): Source
    {
        $baseline                           = null;
        $restrictNotices                    = false;
        $restrictWarnings                   = false;
        $ignoreSuppressionOfDeprecations    = false;
        $ignoreSuppressionOfPhpDeprecations = false;
        $ignoreSuppressionOfErrors          = false;
        $ignoreSuppressionOfNotices         = false;
        $ignoreSuppressionOfPhpNotices      = false;
        $ignoreSuppressionOfWarnings        = false;
        $ignoreSuppressionOfPhpWarnings     = false;
        $ignoreSelfDeprecations             = false;
        $ignoreDirectDeprecations           = false;
        $ignoreIndirectDeprecations         = false;
        $identifyIssueTrigger               = true;

        $element = $this->element($xpath, 'source');

        if ($element !== null) {
            $baseline = $this->parseStringAttribute($element, 'baseline');

            if ($baseline !== null) {
                $baseline = $this->toAbsolutePath($filename, $baseline);
            }

            $restrictNotices                    = $this->parseBooleanAttribute($element, 'restrictNotices', false);
            $restrictWarnings                   = $this->parseBooleanAttribute($element, 'restrictWarnings', false);
            $ignoreSuppressionOfDeprecations    = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfDeprecations', false);
            $ignoreSuppressionOfPhpDeprecations = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfPhpDeprecations', false);
            $ignoreSuppressionOfErrors          = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfErrors', false);
            $ignoreSuppressionOfNotices         = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfNotices', false);
            $ignoreSuppressionOfPhpNotices      = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfPhpNotices', false);
            $ignoreSuppressionOfWarnings        = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfWarnings', false);
            $ignoreSuppressionOfPhpWarnings     = $this->parseBooleanAttribute($element, 'ignoreSuppressionOfPhpWarnings', false);
            $ignoreSelfDeprecations             = $this->parseBooleanAttribute($element, 'ignoreSelfDeprecations', false);
            $ignoreDirectDeprecations           = $this->parseBooleanAttribute($element, 'ignoreDirectDeprecations', false);
            $ignoreIndirectDeprecations         = $this->parseBooleanAttribute($element, 'ignoreIndirectDeprecations', false);
            $identifyIssueTrigger               = $this->parseBooleanAttribute($element, 'identifyIssueTrigger', true);
        }

        $deprecationTriggerElement = $this->element($xpath, 'source/deprecationTrigger');

        $deprecationTriggers = [
            'functions'               => [],
            'methods'                 => [],
            'ignoreUndefinedTriggers' => $deprecationTriggerElement !== null &&
                $this->parseBooleanAttribute($deprecationTriggerElement, 'ignoreUndefinedTriggers', false),
        ];

        $functionNodes = $xpath->query('source/deprecationTrigger/function');

        assert($functionNodes instanceof DOMNodeList);

        foreach ($functionNodes as $functionNode) {
            assert($functionNode instanceof DOMElement);

            $functionName = $functionNode->textContent;

            if ($functionName === '') {
                continue;
            }

            $deprecationTriggers['functions'][] = $functionName;
        }

        $methodNodes = $xpath->query('source/deprecationTrigger/method');

        assert($methodNodes instanceof DOMNodeList);

        foreach ($methodNodes as $methodNode) {
            assert($methodNode instanceof DOMElement);

            $methodName = $methodNode->textContent;

            if ($methodName === '') {
                continue;
            }

            $deprecationTriggers['methods'][] = $methodName;
        }

        $issueTriggerResolvers     = [];
        $issueTriggerResolverNodes = $xpath->query('source/issueTriggerResolvers/issueTriggerResolver');

        assert($issueTriggerResolverNodes instanceof DOMNodeList);

        foreach ($issueTriggerResolverNodes as $node) {
            assert($node instanceof DOMElement);

            $className = $node->getAttribute('className');

            if ($className === '') {
                continue;
            }

            $issueTriggerResolvers[] = $className;
        }

        return new Source(
            $baseline,
            false,
            $this->readFilterDirectories($filename, $xpath, 'source/include/directory'),
            $this->readFilterFiles($filename, $xpath, 'source/include/file'),
            $this->readFilterDirectories($filename, $xpath, 'source/exclude/directory'),
            $this->readFilterFiles($filename, $xpath, 'source/exclude/file'),
            $restrictNotices,
            $restrictWarnings,
            $ignoreSuppressionOfDeprecations,
            $ignoreSuppressionOfPhpDeprecations,
            $ignoreSuppressionOfErrors,
            $ignoreSuppressionOfNotices,
            $ignoreSuppressionOfPhpNotices,
            $ignoreSuppressionOfWarnings,
            $ignoreSuppressionOfPhpWarnings,
            $deprecationTriggers,
            $ignoreSelfDeprecations,
            $ignoreDirectDeprecations,
            $ignoreIndirectDeprecations,
            $identifyIssueTrigger,
            $issueTriggerResolvers,
        );
    }

    private function codeCoverage(string $filename, DOMXPath $xpath): CodeCoverage
    {
        $pathCoverage              = false;
        $branchCoverage            = false;
        $includeUncoveredFiles     = true;
        $ignoreDeprecatedCodeUnits = false;
        $disableCodeCoverageIgnore = false;

        $element = $this->element($xpath, 'coverage');

        if ($element !== null) {
            $pathCoverage = $this->parseBooleanAttribute(
                $element,
                'pathCoverage',
                false,
            );

            $branchCoverage = $this->parseBooleanAttribute(
                $element,
                'branchCoverage',
                false,
            );

            $includeUncoveredFiles = $this->parseBooleanAttribute(
                $element,
                'includeUncoveredFiles',
                true,
            );

            $ignoreDeprecatedCodeUnits = $this->parseBooleanAttribute(
                $element,
                'ignoreDeprecatedCodeUnits',
                false,
            );

            $disableCodeCoverageIgnore = $this->parseBooleanAttribute(
                $element,
                'disableCodeCoverageIgnore',
                false,
            );
        }

        $clover  = null;
        $element = $this->element($xpath, 'coverage/report/clover');

        if ($element !== null) {
            $clover = new Clover(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $cobertura = null;
        $element   = $this->element($xpath, 'coverage/report/cobertura');

        if ($element !== null) {
            $cobertura = new Cobertura(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $crap4j  = null;
        $element = $this->element($xpath, 'coverage/report/crap4j');

        if ($element !== null) {
            $crap4j = new Crap4j(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
                $this->parseNonNegativeIntegerAttribute($element, 'threshold', 30),
            );
        }

        $html    = null;
        $element = $this->element($xpath, 'coverage/report/html');

        if ($element !== null) {
            $defaultColors     = Colors::default();
            $defaultThresholds = Thresholds::default();
            $outputDirectory   = $this->parseStringAttribute($element, 'outputDirectory');

            if ($outputDirectory !== null) {
                $outputDirectory = new Directory(
                    $this->toAbsolutePath(
                        $filename,
                        $outputDirectory,
                    ),
                );
            }

            $html = new CodeCoverageHtml(
                $outputDirectory,
                $this->parseNonNegativeIntegerAttribute($element, 'lowUpperBound', max(0, $defaultThresholds->lowUpperBound())),
                $this->parseNonNegativeIntegerAttribute($element, 'highLowerBound', max(0, $defaultThresholds->highLowerBound())),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessLow', $defaultColors->successLow()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessLowDark', $defaultColors->successLowDark()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessMedium', $defaultColors->successMedium()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessMediumDark', $defaultColors->successMediumDark()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessHigh', $defaultColors->successHigh()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessHighDark', $defaultColors->successHighDark()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessBar', $defaultColors->successBar()),
                $this->parseColorAttributeWithDefault($element, 'colorSuccessBarDark', $defaultColors->successBarDark()),
                $this->parseColorAttributeWithDefault($element, 'colorWarning', $defaultColors->warning()),
                $this->parseColorAttributeWithDefault($element, 'colorWarningDark', $defaultColors->warningDark()),
                $this->parseColorAttributeWithDefault($element, 'colorWarningBar', $defaultColors->warningBar()),
                $this->parseColorAttributeWithDefault($element, 'colorWarningBarDark', $defaultColors->warningBarDark()),
                $this->parseColorAttributeWithDefault($element, 'colorDanger', $defaultColors->danger()),
                $this->parseColorAttributeWithDefault($element, 'colorDangerDark', $defaultColors->dangerDark()),
                $this->parseColorAttributeWithDefault($element, 'colorDangerBar', $defaultColors->dangerBar()),
                $this->parseColorAttributeWithDefault($element, 'colorDangerBarDark', $defaultColors->dangerBarDark()),
                $this->parseColorAttributeWithDefault($element, 'colorBreadcrumbs', $defaultColors->breadcrumbs()),
                $this->parseColorAttributeWithDefault($element, 'colorBreadcrumbsDark', $defaultColors->breadcrumbsDark()),
                $this->parseNullableNonEmptyStringAttribute($element, 'customCssFile'),
            );
        }

        $openClover = null;
        $element    = $this->element($xpath, 'coverage/report/openclover');

        if ($element !== null) {
            $openClover = new OpenClover(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $php     = null;
        $element = $this->element($xpath, 'coverage/report/php');

        if ($element !== null) {
            $php = new CodeCoveragePhp(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
            );
        }

        $text    = null;
        $element = $this->element($xpath, 'coverage/report/text');

        if ($element !== null) {
            $text = new CodeCoverageText(
                new File(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputFile'),
                    ),
                ),
                $this->parseBooleanAttribute($element, 'showUncoveredFiles', false),
                $this->parseBooleanAttribute($element, 'showOnlySummary', false),
            );
        }

        $xml     = null;
        $element = $this->element($xpath, 'coverage/report/xml');

        if ($element !== null) {
            $xml = new CodeCoverageXml(
                new Directory(
                    $this->toAbsolutePath(
                        $filename,
                        (string) $this->parseStringAttribute($element, 'outputDirectory'),
                    ),
                ),
                $this->parseBooleanAttribute($element, 'includeSource', true),
            );
        }

        return new CodeCoverage(
            $pathCoverage,
            $branchCoverage,
            $includeUncoveredFiles,
            $ignoreDeprecatedCodeUnits,
            $disableCodeCoverageIgnore,
            $clover,
            $cobertura,
            $crap4j,
            $html,
            $openClover,
            $php,
            $text,
            $xml,
        );
    }

    private function booleanFromString(string $value, bool $default): bool
    {
        if (strtolower($value) === 'false') {
            return false;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        return $default;
    }

    private function valueFromString(string $value): bool|string
    {
        if (strtolower($value) === 'false') {
            return false;
        }

        if (strtolower($value) === 'true') {
            return true;
        }

        return $value;
    }

    private function readFilterDirectories(string $filename, DOMXPath $xpath, string $query): FilterDirectoryCollection
    {
        $directories = [];

        $directoryNodes = $xpath->query($query);

        assert($directoryNodes instanceof DOMNodeList);

        foreach ($directoryNodes as $directoryNode) {
            assert($directoryNode instanceof DOMElement);

            $directoryPath = $directoryNode->textContent;

            if ($directoryPath === '') {
                continue;
            }

            $prefix = '';

            if ($directoryNode->hasAttribute('prefix')) {
                $prefix = $directoryNode->getAttribute('prefix');
            }

            $suffix = '.php';

            if ($directoryNode->hasAttribute('suffix')) {
                $candidateSuffix = $directoryNode->getAttribute('suffix');

                if ($candidateSuffix !== '') {
                    $suffix = $candidateSuffix;
                }
            }

            $includeInCodeCoverage = !$directoryNode->hasAttribute('includeInCodeCoverage') || $directoryNode->getAttribute('includeInCodeCoverage') !== 'false';

            $directories[] = new FilterDirectory(
                $this->toAbsolutePath($filename, $directoryPath),
                $prefix,
                $suffix,
                $includeInCodeCoverage,
            );
        }

        return FilterDirectoryCollection::fromArray($directories);
    }

    private function readFilterFiles(string $filename, DOMXPath $xpath, string $query): FilterFileCollection
    {
        $files = [];

        $fileNodes = $xpath->query($query);

        assert($fileNodes instanceof DOMNodeList);

        foreach ($fileNodes as $fileNode) {
            assert($fileNode instanceof DOMElement);

            $filePath = $fileNode->textContent;

            if ($filePath !== '') {
                $files[] = new FilterFile(
                    $this->toAbsolutePath($filename, $filePath),
                    !$fileNode->hasAttribute('includeInCodeCoverage') || $fileNode->getAttribute('includeInCodeCoverage') !== 'false',
                );
            }
        }

        return FilterFileCollection::fromArray($files);
    }

    private function groups(DOMXPath $xpath): Groups
    {
        $include = [];
        $exclude = [];

        $groupNodes = $xpath->query('groups/include/group');

        assert($groupNodes instanceof DOMNodeList);

        foreach ($groupNodes as $groupNode) {
            assert($groupNode instanceof DOMNode);

            $groupName = $groupNode->textContent;

            if ($groupName === '') {
                continue;
            }

            $include[] = new Group($groupName);
        }

        $groupNodes = $xpath->query('groups/exclude/group');

        assert($groupNodes instanceof DOMNodeList);

        foreach ($groupNodes as $groupNode) {
            assert($groupNode instanceof DOMNode);

            $groupName = $groupNode->textContent;

            if ($groupName === '') {
                continue;
            }

            $exclude[] = new Group($groupName);
        }

        return new Groups(
            GroupCollection::fromArray($include),
            GroupCollection::fromArray($exclude),
        );
    }

    private function parseBooleanAttribute(DOMElement $element, string $attribute, bool $default): bool
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return $this->booleanFromString(
            $element->getAttribute($attribute),
            false,
        );
    }

    private function parseIntegerAttribute(DOMElement $element, string $attribute, int $default): int
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        return $this->parseInteger(
            $element->getAttribute($attribute),
            $default,
        );
    }

    /**
     * @param non-negative-int $default
     *
     * @return non-negative-int
     */
    private function parseNonNegativeIntegerAttribute(DOMElement $element, string $attribute, int $default): int
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        $value = $element->getAttribute($attribute);

        if (!is_numeric($value)) {
            return $default;
        }

        $intValue = (int) $value;

        if ($intValue < 0) {
            return $default;
        }

        return $intValue;
    }

    /**
     * @param positive-int $default
     *
     * @return positive-int
     */
    private function parsePositiveIntegerAttribute(DOMElement $element, string $attribute, int $default): int
    {
        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        $value = $element->getAttribute($attribute);

        if (!is_numeric($value)) {
            return $default;
        }

        $intValue = (int) $value;

        if ($intValue < 1) {
            return $default;
        }

        return $intValue;
    }

    private function parseStringAttribute(DOMElement $element, string $attribute): ?string
    {
        if (!$element->hasAttribute($attribute)) {
            return null;
        }

        return $element->getAttribute($attribute);
    }

    /**
     * @return null|non-empty-string
     */
    private function parseNullableNonEmptyStringAttribute(DOMElement $element, string $attribute): ?string
    {
        if (!$element->hasAttribute($attribute)) {
            return null;
        }

        $value = $element->getAttribute($attribute);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * @throws Exception
     *
     * @return non-empty-string
     */
    private function parseColorAttributeWithDefault(DOMElement $element, string $attribute, string $default): string
    {
        if ($default === '') {
            throw new Exception(sprintf('Default value for "%s" must not be empty', $attribute));
        }

        if (!$element->hasAttribute($attribute)) {
            return $default;
        }

        $value = $element->getAttribute($attribute);

        if ($value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * @throws Exception
     *
     * @return '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne'
     */
    private function parseVersionOperator(string $operator): string
    {
        return match ($operator) {
            '!=', '<', '<=', '<>', '=', '==', '>', '>=', 'eq', 'ge', 'gt', 'le', 'lt', 'ne' => $operator,
            default                                                                         => throw new Exception(sprintf('Invalid version comparison operator: "%s"', $operator)),
        };
    }

    private function parseInteger(string $value, int $default): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    private function php(string $filename, DOMXPath $xpath): Php
    {
        $includePaths = [];

        $includePathNodes = $xpath->query('php/includePath');

        assert($includePathNodes instanceof DOMNodeList);

        foreach ($includePathNodes as $includePath) {
            assert($includePath instanceof DOMNode);

            $path = $includePath->textContent;

            if ($path !== '') {
                $includePaths[] = new Directory($this->toAbsolutePath($filename, $path));
            }
        }

        $iniSettings = [];

        $iniNodes = $xpath->query('php/ini');

        assert($iniNodes instanceof DOMNodeList);

        foreach ($iniNodes as $ini) {
            assert($ini instanceof DOMElement);

            $iniName = $ini->getAttribute('name');

            if ($iniName === '') {
                continue;
            }

            $iniSettings[] = new IniSetting(
                $iniName,
                $ini->getAttribute('value'),
            );
        }

        $constants = [];

        $constNodes = $xpath->query('php/const');

        assert($constNodes instanceof DOMNodeList);

        foreach ($constNodes as $constNode) {
            assert($constNode instanceof DOMElement);

            $constName = $constNode->getAttribute('name');

            if ($constName === '') {
                continue;
            }

            $value = $constNode->getAttribute('value');

            $constants[] = new Constant(
                $constName,
                $this->valueFromString($value),
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
            $varNodes = $xpath->query('php/' . $array);

            assert($varNodes instanceof DOMNodeList);

            foreach ($varNodes as $var) {
                assert($var instanceof DOMElement);

                $name = $var->getAttribute('name');

                if ($name === '') {
                    continue;
                }

                $value    = $var->getAttribute('value');
                $force    = false;
                $verbatim = false;

                if ($var->hasAttribute('force')) {
                    $force = $this->booleanFromString($var->getAttribute('force'), false);
                }

                if ($var->hasAttribute('verbatim')) {
                    $verbatim = $this->booleanFromString($var->getAttribute('verbatim'), false);
                }

                if (!$verbatim) {
                    $value = $this->valueFromString($value);
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

    private function phpunit(string $filename, DOMDocument $document, DOMXPath $xpath): PHPUnit
    {
        $documentElement = $document->documentElement;

        assert($documentElement !== null);

        $executionOrder      = TestSuiteSorter::ORDER_DEFAULT;
        $defectsFirst        = false;
        $resolveDependencies = $this->parseBooleanAttribute($documentElement, 'resolveDependencies', true);

        if ($documentElement->hasAttribute('executionOrder')) {
            foreach (explode(',', $documentElement->getAttribute('executionOrder')) as $order) {
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
                        $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
                            'Using "duration" for the executionOrder attribute is deprecated and will be removed in PHPUnit 14. Use "duration-ascending" instead.',
                        );

                        break;

                    case 'duration-ascending':
                        $executionOrder = TestSuiteSorter::ORDER_DURATION_ASCENDING;

                        break;

                    case 'duration-descending':
                        $executionOrder = TestSuiteSorter::ORDER_DURATION_DESCENDING;

                        break;

                    case 'random':
                        $executionOrder = TestSuiteSorter::ORDER_RANDOMIZED;

                        break;

                    case 'reverse':
                        $executionOrder = TestSuiteSorter::ORDER_REVERSED;

                        break;

                    case 'size':
                        $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                        EventFacade::emitter()->testRunnerTriggeredPhpunitDeprecation(
                            'Using "size" for the executionOrder attribute is deprecated and will be removed in PHPUnit 14. Use "size-ascending" instead.',
                        );

                        break;

                    case 'size-ascending':
                        $executionOrder = TestSuiteSorter::ORDER_SIZE_ASCENDING;

                        break;

                    case 'size-descending':
                        $executionOrder = TestSuiteSorter::ORDER_SIZE_DESCENDING;

                        break;
                }
            }
        }

        $cacheDirectory = $this->parseStringAttribute($documentElement, 'cacheDirectory');

        if ($cacheDirectory !== null) {
            $cacheDirectory = $this->toAbsolutePath($filename, $cacheDirectory);
        }

        $bootstrap = $this->parseStringAttribute($documentElement, 'bootstrap');

        if ($bootstrap !== null) {
            $bootstrap = $this->toAbsolutePath($filename, $bootstrap);
        }

        $extensionsDirectory = $this->parseStringAttribute($documentElement, 'extensionsDirectory');

        if ($extensionsDirectory !== null) {
            $extensionsDirectory = $this->toAbsolutePath($filename, $extensionsDirectory);
        }

        $backupStaticProperties = false;

        if ($documentElement->hasAttribute('backupStaticProperties')) {
            $backupStaticProperties = $this->parseBooleanAttribute($documentElement, 'backupStaticProperties', false);
        }

        $requireCoverageMetadata = false;

        if ($documentElement->hasAttribute('requireCoverageMetadata')) {
            $requireCoverageMetadata = $this->parseBooleanAttribute($documentElement, 'requireCoverageMetadata', false);
        }

        $requireSealedMockObjects = false;

        if ($documentElement->hasAttribute('requireSealedMockObjects')) {
            $requireSealedMockObjects = $this->parseBooleanAttribute($documentElement, 'requireSealedMockObjects', false);
        }

        $beStrictAboutCoverageMetadata = false;

        if ($documentElement->hasAttribute('beStrictAboutCoverageMetadata')) {
            $beStrictAboutCoverageMetadata = $this->parseBooleanAttribute($documentElement, 'beStrictAboutCoverageMetadata', false);
        }

        $requireCoverageContribution = false;

        if ($documentElement->hasAttribute('requireCoverageContribution')) {
            $requireCoverageContribution = $this->parseBooleanAttribute($documentElement, 'requireCoverageContribution', false);
        }

        $shortenArraysForExportThreshold = $this->parseIntegerAttribute($documentElement, 'shortenArraysForExportThreshold', 10);

        if ($shortenArraysForExportThreshold < 0) {
            $shortenArraysForExportThreshold = 0;
        }

        return new PHPUnit(
            $cacheDirectory,
            $this->parseBooleanAttribute($documentElement, 'cacheResult', true),
            $this->parseColumns($document),
            $this->parseColors($document),
            $this->parseBooleanAttribute($documentElement, 'stderr', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnAllIssues', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnIncompleteTests', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnSkippedTests', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnTestsThatTriggerDeprecations', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnPhpunitDeprecations', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnPhpunitNotices', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnTestsThatTriggerErrors', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnTestsThatTriggerNotices', false),
            $this->parseBooleanAttribute($documentElement, 'displayDetailsOnTestsThatTriggerWarnings', false),
            $this->parseBooleanAttribute($documentElement, 'reverseDefectList', false),
            $requireCoverageMetadata,
            $requireSealedMockObjects,
            $bootstrap,
            $this->bootstrapForTestSuite($filename, $xpath),
            $this->parseBooleanAttribute($documentElement, 'processIsolation', false),
            $this->parseBooleanAttribute($documentElement, 'failOnAllIssues', false),
            $this->parseBooleanAttribute($documentElement, 'failOnDeprecation', false),
            $this->parseBooleanAttribute($documentElement, 'failOnPhpunitDeprecation', false),
            $this->parseBooleanAttribute($documentElement, 'failOnPhpunitNotice', false),
            $this->parseBooleanAttribute($documentElement, 'failOnPhpunitWarning', true),
            $this->parseBooleanAttribute($documentElement, 'failOnEmptyTestSuite', false),
            $documentElement->hasAttribute('failOnEmptyTestSuite'),
            $this->parseBooleanAttribute($documentElement, 'failOnIncomplete', false),
            $this->parseBooleanAttribute($documentElement, 'failOnNotice', false),
            $this->parseBooleanAttribute($documentElement, 'failOnRisky', false),
            $this->parseBooleanAttribute($documentElement, 'failOnSkipped', false),
            $this->parseBooleanAttribute($documentElement, 'failOnWarning', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnDefect', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnDeprecation', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnError', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnFailure', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnIncomplete', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnNotice', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnRisky', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnSkipped', false),
            (int) $this->parseBooleanAttribute($documentElement, 'stopOnWarning', false),
            $extensionsDirectory,
            $this->parseBooleanAttribute($documentElement, 'beStrictAboutChangesToGlobalState', false),
            $this->parseBooleanAttribute($documentElement, 'beStrictAboutOutputDuringTests', false),
            $this->parseBooleanAttribute($documentElement, 'beStrictAboutTestsThatDoNotTestAnything', true),
            $beStrictAboutCoverageMetadata,
            $requireCoverageContribution,
            $this->parseBooleanAttribute($documentElement, 'enforceTimeLimit', false),
            $this->parseNonNegativeIntegerAttribute($documentElement, 'defaultTimeLimit', 1),
            $this->parsePositiveIntegerAttribute($documentElement, 'timeoutForSmallTests', 1),
            $this->parsePositiveIntegerAttribute($documentElement, 'timeoutForMediumTests', 10),
            $this->parsePositiveIntegerAttribute($documentElement, 'timeoutForLargeTests', 60),
            $this->parseNullableNonEmptyStringAttribute($documentElement, 'defaultTestSuite'),
            $executionOrder,
            $resolveDependencies,
            $defectsFirst,
            $this->parseBooleanAttribute($documentElement, 'backupGlobals', false),
            $backupStaticProperties,
            $this->parseBooleanAttribute($documentElement, 'testdox', false),
            $this->parseBooleanAttribute($documentElement, 'testdoxSummary', false),
            $this->parseBooleanAttribute($documentElement, 'controlGarbageCollector', false),
            $this->parsePositiveIntegerAttribute($documentElement, 'numberOfTestsBeforeGarbageCollection', 100),
            $shortenArraysForExportThreshold,
            $this->parsePositiveIntegerAttribute($documentElement, 'diffContext', 3),
        );
    }

    /**
     * @return non-empty-string
     */
    private function parseColors(DOMDocument $document): string
    {
        $documentElement = $document->documentElement;

        assert($documentElement !== null);

        $colors = Configuration::COLOR_DEFAULT;

        if ($documentElement->hasAttribute('colors')) {
            if ($this->booleanFromString($documentElement->getAttribute('colors'), false)) {
                $colors = Configuration::COLOR_ALWAYS;
            } else {
                $colors = Configuration::COLOR_NEVER;
            }
        }

        return $colors;
    }

    private function parseColumns(DOMDocument $document): int|string
    {
        $documentElement = $document->documentElement;

        assert($documentElement !== null);

        $columns = 80;

        if ($documentElement->hasAttribute('columns')) {
            $columns = $documentElement->getAttribute('columns');

            if ($columns !== 'max') {
                $columns = $this->parseInteger($columns, 80);
            }
        }

        return $columns;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function bootstrapForTestSuite(string $filename, DOMXPath $xpath): array
    {
        $bootstrapForTestSuite = [];

        foreach ($this->parseTestSuiteElements($xpath) as $element) {
            if (!$element->hasAttribute('bootstrap')) {
                continue;
            }

            $name      = $element->getAttribute('name');
            $bootstrap = $element->getAttribute('bootstrap');

            assert($name !== '');
            assert($bootstrap !== '');

            $bootstrapForTestSuite[$name] = $this->toAbsolutePath($filename, $bootstrap);
        }

        return $bootstrapForTestSuite;
    }

    private function testSuite(string $filename, DOMXPath $xpath): TestSuiteCollection
    {
        $testSuites = [];

        foreach ($this->parseTestSuiteElements($xpath) as $element) {
            $exclude = [];

            foreach ($element->getElementsByTagName('exclude') as $excludeNode) {
                $excludeFile = $excludeNode->textContent;

                if ($excludeFile !== '') {
                    $exclude[] = new File($this->toAbsolutePath($filename, $excludeFile));
                }
            }

            $directories = [];

            foreach ($element->getElementsByTagName('directory') as $directoryNode) {
                assert($directoryNode instanceof DOMElement);

                $directory = $directoryNode->textContent;

                if ($directory === '') {
                    continue;
                }

                $prefix = '';

                if ($directoryNode->hasAttribute('prefix')) {
                    $prefix = $directoryNode->getAttribute('prefix');
                }

                $suffix = 'Test.php';

                if ($directoryNode->hasAttribute('suffix')) {
                    $candidateSuffix = $directoryNode->getAttribute('suffix');

                    if ($candidateSuffix !== '') {
                        $suffix = $candidateSuffix;
                    }
                }

                $phpVersion = PHP_VERSION;

                if ($directoryNode->hasAttribute('phpVersion')) {
                    $phpVersion = $directoryNode->getAttribute('phpVersion');
                }

                $phpVersionOperator = new VersionComparisonOperator('>=');

                if ($directoryNode->hasAttribute('phpVersionOperator')) {
                    $phpVersionOperator = new VersionComparisonOperator(
                        $this->parseVersionOperator($directoryNode->getAttribute('phpVersionOperator')),
                    );
                }

                $groups = [];

                if ($directoryNode->hasAttribute('groups')) {
                    foreach (explode(',', $directoryNode->getAttribute('groups')) as $group) {
                        $group = trim($group);

                        if ($group === '') {
                            continue;
                        }

                        $groups[] = $group;
                    }
                }

                $directories[] = new TestDirectory(
                    $this->toAbsolutePath($filename, $directory),
                    $prefix,
                    $suffix,
                    $phpVersion,
                    $phpVersionOperator,
                    $groups,
                );
            }

            $files = [];

            foreach ($element->getElementsByTagName('file') as $fileNode) {
                assert($fileNode instanceof DOMElement);

                $file = $fileNode->textContent;

                if ($file === '') {
                    continue;
                }

                $phpVersion = PHP_VERSION;

                if ($fileNode->hasAttribute('phpVersion')) {
                    $phpVersion = $fileNode->getAttribute('phpVersion');
                }

                $phpVersionOperator = new VersionComparisonOperator('>=');

                if ($fileNode->hasAttribute('phpVersionOperator')) {
                    $phpVersionOperator = new VersionComparisonOperator(
                        $this->parseVersionOperator($fileNode->getAttribute('phpVersionOperator')),
                    );
                }

                $groups = [];

                if ($fileNode->hasAttribute('groups')) {
                    foreach (explode(',', $fileNode->getAttribute('groups')) as $group) {
                        $group = trim($group);

                        if ($group === '') {
                            continue;
                        }

                        $groups[] = $group;
                    }
                }

                $files[] = new TestFile(
                    $this->toAbsolutePath($filename, $file),
                    $phpVersion,
                    $phpVersionOperator,
                    $groups,
                );
            }

            $name = $element->getAttribute('name');

            assert($name !== '');

            $testSuites[] = new TestSuiteConfiguration(
                $name,
                TestDirectoryCollection::fromArray($directories),
                TestFileCollection::fromArray($files),
                FileCollection::fromArray($exclude),
            );
        }

        return TestSuiteCollection::fromArray($testSuites);
    }

    /**
     * @return list<DOMElement>
     */
    private function parseTestSuiteElements(DOMXPath $xpath): array
    {
        $elements = [];

        $testSuiteNodes = $xpath->query('testsuites/testsuite');

        assert($testSuiteNodes instanceof DOMNodeList);

        if ($testSuiteNodes->length === 0) {
            $testSuiteNodes = $xpath->query('testsuite');

            assert($testSuiteNodes instanceof DOMNodeList);
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

        assert($nodes instanceof DOMNodeList);

        if ($nodes->length === 1) {
            $node = $nodes->item(0);

            assert($node instanceof DOMElement);

            return $node;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function ensureConfigurationValidatesAgainstAtLeastOneSchema(DOMDocument $document, string $configurationFile, ValidationResult $validationResult): void
    {
        $documentElement = $document->documentElement;

        assert($documentElement !== null);

        if ($documentElement->localName === 'phpunit') {
            return;
        }

        $schemaFinder = new SchemaFinder;
        $validator    = new Validator;

        foreach ($schemaFinder->available() as $version) {
            try {
                $xsdFilename = $schemaFinder->find($version);
            } catch (CannotFindSchemaException) {
                continue;
            }

            if (!$validator->validate($document, $xsdFilename)->hasValidationErrors()) {
                return;
            }
        }

        throw new Exception(
            sprintf(
                'XML configuration file %s does not validate against any supported PHPUnit schema:' . PHP_EOL . '%s',
                $configurationFile,
                $validationResult->asString(),
            ),
        );
    }
}
