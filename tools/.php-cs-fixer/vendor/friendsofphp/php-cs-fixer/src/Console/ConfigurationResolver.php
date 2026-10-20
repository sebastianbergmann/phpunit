<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console;

use Ergebnis\AgentDetector;
use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\Cache\Directory;
use PhpCsFixer\Cache\DirectoryInterface;
use PhpCsFixer\Cache\FileCacheManager;
use PhpCsFixer\Cache\FileHandler;
use PhpCsFixer\Cache\NullCacheManager;
use PhpCsFixer\Cache\Signature;
use PhpCsFixer\Config\NullRuleCustomisationPolicy;
use PhpCsFixer\Config\RuleCustomisationPolicyAwareConfigInterface;
use PhpCsFixer\Config\RuleCustomisationPolicyInterface;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Console\Output\Progress\ProgressOutputType;
use PhpCsFixer\Console\Report\FixReport\ReporterFactory;
use PhpCsFixer\Console\Report\FixReport\ReporterInterface;
use PhpCsFixer\CustomRulesetsAwareConfigInterface;
use PhpCsFixer\Differ\DifferInterface;
use PhpCsFixer\Differ\NullDiffer;
use PhpCsFixer\Differ\UnifiedDiffer;
use PhpCsFixer\Finder;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\Future;
use PhpCsFixer\Linter\Linter;
use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\ParallelAwareConfigInterface;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\RuleSet\RuleSetInterface;
use PhpCsFixer\RuleSet\RuleSets;
use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixer\StdinFileInfo;
use PhpCsFixer\ToolInfoInterface;
use PhpCsFixer\UnsupportedPhpVersionAllowedConfigInterface;
use PhpCsFixer\Utils;
use PhpCsFixer\WhitespacesFixerConfig;
use PhpCsFixer\WordMatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * The resolver that resolves configuration to use by command line options and config.
 *
 * @internal
 *
 * @phpstan-type _Options array{
 *      allow-risky: null|string,
 *      cache-file: null|string,
 *      config: null|string,
 *      diff: null|string,
 *      dry-run: null|bool,
 *      format: null|string,
 *      path: list<string>,
 *      path-mode: value-of<self::PATH_MODE_VALUES>,
 *      rules: null|string,
 *      sequential: null|string,
 *      show-progress: null|string,
 *      stop-on-violation: null|bool,
 *      using-cache: null|string,
 *      allow-unsupported-php-version: null|bool,
 *      verbosity: null|string,
 *  }
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ConfigurationResolver
{
    public const IGNORE_CONFIG_FILE = '-';

    public const PATH_MODE_OVERRIDE = 'override';
    public const PATH_MODE_INTERSECTION = 'intersection';
    public const PATH_MODE_VALUES = [
        self::PATH_MODE_OVERRIDE,
        self::PATH_MODE_INTERSECTION,
    ];

    public const BOOL_YES = 'yes';
    public const BOOL_NO = 'no';
    public const BOOL_VALUES = [
        self::BOOL_YES,
        self::BOOL_NO,
    ];

    /**
     * @TODO v4: this is no longer needed due to `MARKER-multi-paths-vs-only-cwd-config`
     */
    private ?string $deprecatedNestedConfigDir = null;

    private ?bool $allowRisky = null;

    private ?ConfigInterface $config = null;

    private ?string $configFile = null;

    private string $cwd;

    private ConfigInterface $defaultConfig;

    private ?ReporterInterface $reporter = null;

    private ?bool $isStdIn = null;

    private ?bool $isDryRun = null;

    /**
     * @var null|list<FixerInterface>
     */
    private ?array $fixers = null;

    private ?bool $configFinderIsOverridden = null;

    private ?bool $configRulesAreOverridden = null;

    private ToolInfoInterface $toolInfo;

    /**
     * @var _Options
     */
    private array $options = [
        'allow-risky' => null,
        'cache-file' => null,
        'config' => null,
        'diff' => null,
        'dry-run' => null,
        'format' => null,
        'path' => [],
        'path-mode' => self::PATH_MODE_OVERRIDE,
        'rules' => null,
        'sequential' => null,
        'show-progress' => null,
        'stop-on-violation' => null,
        'using-cache' => null,
        'allow-unsupported-php-version' => null,
        'verbosity' => null,
    ];

    private ?string $cacheFile = null;

    private ?CacheManagerInterface $cacheManager = null;

    private ?DifferInterface $differ = null;

    private ?Directory $directory = null;

    /**
     * @var null|iterable<\SplFileInfo>
     */
    private ?iterable $finder = null;

    private ?string $format = null;

    private ?Linter $linter = null;

    /**
     * @var null|list<string>
     */
    private ?array $path = null;

    /**
     * @var null|ProgressOutputType::*
     */
    private $progress;

    private ?RuleSet $ruleSet = null;

    private ?bool $usingCache = null;

    private ?bool $isUnsupportedPhpVersionAllowed = null;

    private ?RuleCustomisationPolicyInterface $ruleCustomisationPolicy = null;

    private ?FixerFactory $fixerFactory = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ConfigInterface $config,
        array $options,
        string $cwd,
        ToolInfoInterface $toolInfo
    ) {
        $this->defaultConfig = $config;
        $this->cwd = $cwd;
        $this->toolInfo = $toolInfo;

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getCacheFile(): ?string
    {
        if (!$this->getUsingCache()) {
            return null;
        }

        if (null === $this->cacheFile) {
            if (null === $this->options['cache-file']) {
                $this->cacheFile = $this->getConfig()->getCacheFile();
            } else {
                $this->cacheFile = $this->options['cache-file'];
            }
        }

        return $this->cacheFile;
    }

    public function getCacheManager(): CacheManagerInterface
    {
        if (null === $this->cacheManager) {
            $cacheFile = $this->getCacheFile();

            if (null === $cacheFile) {
                $this->cacheManager = new NullCacheManager();
            } else {
                $this->cacheManager = new FileCacheManager(
                    new FileHandler($cacheFile),
                    new Signature(
                        \PHP_VERSION,
                        $this->toolInfo->getVersion(),
                        $this->getConfig()->getIndent(),
                        $this->getConfig()->getLineEnding(),
                        $this->getRules(),
                        $this->getRuleCustomisationPolicy()->getPolicyVersionForCache(),
                    ),
                    $this->isDryRun(),
                    $this->getDirectory(),
                );
            }
        }

        return $this->cacheManager;
    }

    public function getConfig(): ConfigInterface
    {
        if (null === $this->config) {
            foreach ($this->computeConfigFiles() as $configFile) {
                if (!file_exists($configFile)) {
                    continue;
                }

                $configFileBasename = basename($configFile);

                /** @TODO v4 drop handling (triggering error) for v2 config names */
                $deprecatedConfigs = [
                    '.php_cs' => '.php-cs-fixer.php',
                    '.php_cs.dist' => '.php-cs-fixer.dist.php',
                ];

                if (isset($deprecatedConfigs[$configFileBasename])) {
                    throw new InvalidConfigurationException("Configuration file `{$configFileBasename}` is outdated, rename to `{$deprecatedConfigs[$configFileBasename]}`.");
                }

                if (null !== $this->deprecatedNestedConfigDir && str_starts_with($configFile, $this->deprecatedNestedConfigDir)) {
                    // @TODO v4: when removing, remove also TODO with `MARKER-multi-paths-vs-only-cwd-config`
                    Future::triggerDeprecation(
                        new InvalidConfigurationException("Configuration file `{$configFile}` is picked as file inside passed `path` CLI argument. This will be ignored in the future and only config file in `cwd` will be picked. Please use `config` CLI option instead if you want to keep current behaviour."),
                    );
                }

                $this->config = self::separatedContextLessInclude($configFile);
                $this->configFile = $configFile;

                break;
            }

            if (null === $this->config) {
                $this->config = $this->defaultConfig;
            }

            if ($this->config instanceof CustomRulesetsAwareConfigInterface) {
                foreach ($this->config->getCustomRuleSets() as $ruleSet) {
                    RuleSets::registerCustomRuleSet($ruleSet);
                }
            }
        }

        return $this->config;
    }

    public function getParallelConfig(): ParallelConfig
    {
        $config = $this->getConfig();

        return true !== $this->options['sequential'] && $config instanceof ParallelAwareConfigInterface
            ? $config->getParallelConfig()
            : ParallelConfigFactory::sequential();
    }

    public function getConfigFile(): ?string
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }

        return $this->configFile;
    }

    public function getDiffer(): DifferInterface
    {
        if (null === $this->differ) {
            $this->differ = (true === $this->options['diff']) ? new UnifiedDiffer() : new NullDiffer();
        }

        return $this->differ;
    }

    public function getDirectory(): DirectoryInterface
    {
        if (null === $this->directory) {
            $cwd = realpath($this->cwd);

            $this->directory = new Directory(false !== $cwd ? $cwd : $this->cwd);
        }

        return $this->directory;
    }

    /**
     * @return list<FixerInterface>
     */
    public function getFixers(): array
    {
        if (null === $this->fixers) {
            $this->fixers = $this->createFixerFactory()
                ->useRuleSet($this->getRuleSet())
                ->setWhitespacesConfig(new WhitespacesFixerConfig($this->config->getIndent(), $this->config->getLineEnding()))
                ->getFixers()
            ;

            if (false === $this->getRiskyAllowed()) {
                $riskyFixers = array_map(
                    static fn (FixerInterface $fixer): string => $fixer->getName(),
                    array_values(array_filter(
                        $this->fixers,
                        static fn (FixerInterface $fixer): bool => $fixer->isRisky(),
                    )),
                );

                if (\count($riskyFixers) > 0) {
                    throw new InvalidConfigurationException(\sprintf('The rules contain risky fixers (%s), but they are not allowed to run. Perhaps you forget to use --allow-risky=yes option?', Utils::naturalLanguageJoin($riskyFixers)));
                }
            }
        }

        return $this->fixers;
    }

    public function getLinter(): LinterInterface
    {
        if (null === $this->linter) {
            $this->linter = new Linter();
        }

        return $this->linter;
    }

    /**
     * Returns path.
     *
     * @return list<string>
     */
    public function getPath(): array
    {
        if (null === $this->path) {
            $filesystem = new Filesystem();
            $cwd = $this->cwd;

            if (1 === \count($this->options['path']) && '-' === $this->options['path'][0]) {
                $this->path = $this->options['path'];
            } else {
                $this->path = array_map(
                    static function (string $rawPath) use ($cwd, $filesystem): string {
                        $path = trim($rawPath);

                        if ('' === $path) {
                            throw new InvalidConfigurationException("Invalid path: \"{$rawPath}\".");
                        }

                        $absolutePath = $filesystem->isAbsolutePath($path)
                            ? $path
                            : $cwd.\DIRECTORY_SEPARATOR.$path;

                        if (!file_exists($absolutePath)) {
                            throw new InvalidConfigurationException(\sprintf(
                                'The path "%s" is not readable.',
                                $path,
                            ));
                        }

                        return $absolutePath;
                    },
                    $this->options['path'],
                );
            }
        }

        return $this->path;
    }

    /**
     * @return ProgressOutputType::*
     *
     * @throws InvalidConfigurationException
     */
    public function getProgressType(): string
    {
        if (null === $this->progress) {
            if ('txt' === $this->resolveFormat()) {
                $progressType = $this->options['show-progress'];

                if (null === $progressType) {
                    $progressType = $this->getConfig()->getHideProgress()
                        ? ProgressOutputType::NONE
                        : ProgressOutputType::BAR;
                } elseif (!\in_array($progressType, ProgressOutputType::all(), true)) {
                    throw new InvalidConfigurationException(\sprintf(
                        'The progress type "%s" is not defined, supported are %s.',
                        $progressType,
                        Utils::naturalLanguageJoin(ProgressOutputType::all()),
                    ));
                }

                $this->progress = $progressType;
            } else {
                $this->progress = ProgressOutputType::NONE;
            }
        }

        return $this->progress;
    }

    public function getReporter(): ReporterInterface
    {
        if (null === $this->reporter) {
            $reporterFactory = new ReporterFactory();
            $reporterFactory->registerBuiltInReporters();

            $format = $this->resolveFormat();

            try {
                $this->reporter = $reporterFactory->getReporter($format);
            } catch (\UnexpectedValueException $e) {
                $formats = $reporterFactory->getFormats();
                sort($formats);

                throw new InvalidConfigurationException(\sprintf('The format "%s" is not defined, supported are %s.', $format, Utils::naturalLanguageJoin($formats)));
            }
        }

        return $this->reporter;
    }

    public function getRiskyAllowed(): bool
    {
        if (null === $this->allowRisky) {
            if (null === $this->options['allow-risky']) {
                $this->allowRisky = $this->getConfig()->getRiskyAllowed();
            } else {
                $this->allowRisky = $this->resolveOptionBooleanValue('allow-risky');
            }
        }

        return $this->allowRisky;
    }

    /**
     * Returns rules.
     *
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array
    {
        return $this->getRuleSet()->getRules();
    }

    public function getUsingCache(): bool
    {
        if (null === $this->usingCache) {
            if (null === $this->options['using-cache']) {
                $this->usingCache = $this->getConfig()->getUsingCache();
            } else {
                $this->usingCache = $this->resolveOptionBooleanValue('using-cache');
            }
        }

        $this->usingCache = $this->usingCache && $this->isCachingAllowedForRuntime();

        return $this->usingCache;
    }

    public function getUnsupportedPhpVersionAllowed(): bool
    {
        if (null === $this->isUnsupportedPhpVersionAllowed) {
            if (null === $this->options['allow-unsupported-php-version']) {
                $config = $this->getConfig();
                $this->isUnsupportedPhpVersionAllowed = $config instanceof UnsupportedPhpVersionAllowedConfigInterface
                    ? $config->getUnsupportedPhpVersionAllowed()
                    : false;
            } else {
                $this->isUnsupportedPhpVersionAllowed = $this->resolveOptionBooleanValue('allow-unsupported-php-version');
            }
        }

        return $this->isUnsupportedPhpVersionAllowed;
    }

    public function getRuleCustomisationPolicy(): RuleCustomisationPolicyInterface
    {
        if (null === $this->ruleCustomisationPolicy) {
            $config = $this->getConfig();
            if ($config instanceof RuleCustomisationPolicyAwareConfigInterface) {
                $this->ruleCustomisationPolicy = $config->getRuleCustomisationPolicy();
            }
            $this->ruleCustomisationPolicy ??= new NullRuleCustomisationPolicy();
        }

        return $this->ruleCustomisationPolicy;
    }

    /**
     * @return iterable<\SplFileInfo>
     */
    public function getFinder(): iterable
    {
        if (null === $this->finder) {
            $this->finder = $this->resolveFinder();
        }

        return $this->finder;
    }

    /**
     * Returns dry-run flag.
     */
    public function isDryRun(): bool
    {
        if (null === $this->isDryRun) {
            if ($this->isStdIn()) {
                // Can't write to STDIN
                $this->isDryRun = true;
            } else {
                $this->isDryRun = $this->options['dry-run'];
            }
        }

        return $this->isDryRun;
    }

    public function shouldStopOnViolation(): bool
    {
        return $this->options['stop-on-violation'];
    }

    public function configFinderIsOverridden(): bool
    {
        if (null === $this->configFinderIsOverridden) {
            $this->resolveFinder();
        }

        return $this->configFinderIsOverridden;
    }

    public function configRulesAreOverridden(): bool
    {
        if (null === $this->configRulesAreOverridden) {
            $this->parseRules();
        }

        return $this->configRulesAreOverridden;
    }

    /**
     * Compute file candidates for config file.
     *
     * @TODO v4: don't offer configs from passed `path` CLI argument
     *
     * @return list<string>
     */
    private function computeConfigFiles(): array
    {
        $configFile = $this->options['config'];

        if (self::IGNORE_CONFIG_FILE === $configFile) {
            return [];
        }

        if (null !== $configFile) {
            if (false === file_exists($configFile) || false === is_readable($configFile)) {
                throw new InvalidConfigurationException(\sprintf('Cannot read config file "%s".', $configFile));
            }

            return [$configFile];
        }

        $path = $this->getPath();

        if ($this->isStdIn() || 0 === \count($path)) {
            $configDir = $this->cwd;
        } elseif (1 < \count($path)) {
            // @TODO v4: this is no longer needed due to `MARKER-multi-paths-vs-only-cwd-config`
            throw new InvalidConfigurationException('For multiple paths config parameter is required.');
        } elseif (!is_file($path[0])) {
            $configDir = $path[0];
        } else {
            $dirName = pathinfo($path[0], \PATHINFO_DIRNAME);
            $configDir = is_dir($dirName) ? $dirName : $path[0];
        }

        $candidates = [
            $configDir.\DIRECTORY_SEPARATOR.'.php-cs-fixer.php',
            $configDir.\DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php',

            // @TODO v4 drop handling (triggering error) for v2 config names
            $configDir.\DIRECTORY_SEPARATOR.'.php_cs', // old v2 config, present here only to throw nice error message later
            $configDir.\DIRECTORY_SEPARATOR.'.php_cs.dist', // old v2 config, present here only to throw nice error message later
        ];

        if ($configDir !== $this->cwd) {
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php-cs-fixer.php';
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php';

            // @TODO v4 drop handling (triggering error) for v2 config names
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php_cs'; // old v2 config, present here only to throw nice error message later
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php_cs.dist'; // old v2 config, present here only to throw nice error message later

            $this->deprecatedNestedConfigDir = $configDir;
        }

        return $candidates;
    }

    private function createFixerFactory(): FixerFactory
    {
        if (null === $this->fixerFactory) {
            $fixerFactory = new FixerFactory();
            $fixerFactory->registerBuiltInFixers();
            $fixerFactory->registerCustomFixers($this->getConfig()->getCustomFixers());

            $this->fixerFactory = $fixerFactory;
        }

        return $this->fixerFactory;
    }

    private function resolveFormat(): string
    {
        if (null === $this->format) {
            $formatCandidate = $this->options['format'] ?? $this->getConfig()->getFormat();
            $parts = explode(',', $formatCandidate);

            if (\count($parts) > 2) {
                throw new InvalidConfigurationException(\sprintf('The format "%s" is invalid.', $formatCandidate));
            }

            $this->format = $parts[0];

            if ('@auto' === $this->format) {
                if (filter_var(getenv('GITLAB_CI'), \FILTER_VALIDATE_BOOL)) {
                    $this->format = 'gitlab';

                    return $this->format;
                }

                $agentDetector = new AgentDetector\Detector();

                if ($agentDetector->isAgentPresent(array_fill_keys(array_keys(getenv()), ''))) {
                    $this->format = 'json';

                    return $this->format;
                }

                $this->format = $parts[1] ?? 'txt';
            }
        }

        return $this->format;
    }

    private function getRuleSet(): RuleSetInterface
    {
        if (null === $this->ruleSet) {
            $rules = $this->parseRules();
            $this->validateRules($rules);

            $this->ruleSet = new RuleSet($rules);
        }

        return $this->ruleSet;
    }

    private function isStdIn(): bool
    {
        if (null === $this->isStdIn) {
            $this->isStdIn = 1 === \count($this->options['path']) && '-' === $this->options['path'][0];
        }

        return $this->isStdIn;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseRules(): array
    {
        $this->configRulesAreOverridden = null !== $this->options['rules'];

        if (null === $this->options['rules']) {
            $this->configRulesAreOverridden = false;

            return $this->getConfig()->getRules();
        }

        $rules = trim($this->options['rules']);
        if ('' === $rules) {
            throw new InvalidConfigurationException('Empty rules value is not allowed.');
        }

        if (str_starts_with($rules, '{')) {
            try {
                return json_decode($rules, true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new InvalidConfigurationException(\sprintf('Invalid JSON rules input: "%s".', $e->getMessage()));
            }
        }

        $rules = [];

        foreach (explode(',', $this->options['rules']) as $rule) {
            $rule = trim($rule);

            if ('' === $rule) {
                throw new InvalidConfigurationException('Empty rule name is not allowed.');
            }

            if (str_starts_with($rule, '-')) {
                $rules[substr($rule, 1)] = false;
            } else {
                $rules[$rule] = true;
            }
        }

        $this->configRulesAreOverridden = true;

        return $rules;
    }

    /**
     * @param array<string, mixed> $rules
     *
     * @throws InvalidConfigurationException
     */
    private function validateRules(array $rules): void
    {
        /**
         * Create a ruleset that contains all configured rules, even when they originally have been disabled.
         *
         * @see RuleSet::resolveSet()
         */
        $ruleSet = [];

        foreach ($rules as $key => $value) {
            if (\is_int($key)) {
                throw new InvalidConfigurationException(\sprintf('Missing value for "%s" rule/set.', $value));
            }

            $ruleSet[$key] = true;
        }

        $ruleSet = new RuleSet($ruleSet);

        $configuredFixers = array_keys($ruleSet->getRules());

        $fixers = $this->createFixerFactory()->getFixers();

        $availableFixers = array_map(static fn (FixerInterface $fixer): string => $fixer->getName(), $fixers);

        $unknownFixers = array_diff($configuredFixers, $availableFixers);

        if (\count($unknownFixers) > 0) {
            /**
             * @TODO v4: `renamedRulesFromV2ToV3` no longer needed
             * @TODO v3.99: decide how to handle v3 to v4 (where legacy rules are already removed)
             */
            $renamedRulesFromV2ToV3 = [
                'blank_line_before_return' => [
                    'new_name' => 'blank_line_before_statement',
                    'config' => ['statements' => ['return']],
                ],
                'final_static_access' => [
                    'new_name' => 'self_static_accessor',
                ],
                'hash_to_slash_comment' => [
                    'new_name' => 'single_line_comment_style',
                    'config' => ['comment_types' => ['hash']],
                ],
                'lowercase_constants' => [
                    'new_name' => 'constant_case',
                    'config' => ['case' => 'lower'],
                ],
                'no_extra_consecutive_blank_lines' => [
                    'new_name' => 'no_extra_blank_lines',
                ],
                'no_multiline_whitespace_before_semicolons' => [
                    'new_name' => 'multiline_whitespace_before_semicolons',
                ],
                'no_short_echo_tag' => [
                    'new_name' => 'echo_tag_syntax',
                    'config' => ['format' => 'long'],
                ],
                'php_unit_ordered_covers' => [
                    'new_name' => 'phpdoc_order_by_value',
                    'config' => ['annotations' => ['covers']],
                ],
                'phpdoc_inline_tag' => [
                    'new_name' => 'general_phpdoc_tag_rename, phpdoc_inline_tag_normalizer and phpdoc_tag_type',
                ],
                'pre_increment' => [
                    'new_name' => 'increment_style',
                    'config' => ['style' => 'pre'],
                ],
                'psr0' => [
                    'new_name' => 'psr_autoloading',
                    'config' => ['dir' => 'x'],
                ],
                'psr4' => [
                    'new_name' => 'psr_autoloading',
                ],
                'silenced_deprecation_error' => [
                    'new_name' => 'error_suppression',
                ],
                'trailing_comma_in_multiline_array' => [
                    'new_name' => 'trailing_comma_in_multiline',
                    'config' => ['elements' => ['arrays']],
                ],
            ];

            $message = 'The rules contain unknown fixers: ';
            $hasOldRule = false;

            foreach ($unknownFixers as $unknownFixer) {
                if (isset($renamedRulesFromV2ToV3[$unknownFixer])) { // Check if present as old renamed rule
                    $hasOldRule = true;
                    $message .= \sprintf(
                        '"%s" is renamed (did you mean "%s"?%s), ',
                        $unknownFixer,
                        $renamedRulesFromV2ToV3[$unknownFixer]['new_name'],
                        isset($renamedRulesFromV2ToV3[$unknownFixer]['config']) ? ' (note: use configuration "'.Utils::toString($renamedRulesFromV2ToV3[$unknownFixer]['config']).'")' : '',
                    );
                } else { // Go to normal matcher if it is not a renamed rule
                    $matcher = new WordMatcher($availableFixers);
                    $alternative = $matcher->match($unknownFixer);
                    $message .= \sprintf(
                        '"%s"%s, ',
                        $unknownFixer,
                        null === $alternative ? '' : ' (did you mean "'.$alternative.'"?)',
                    );
                }
            }

            $message = substr($message, 0, -2).'.';

            if ($hasOldRule) {
                $message .= "\nFor more info about updating see: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.0.0/UPGRADE-v3.md#renamed-ruless.";
            }

            throw new InvalidConfigurationException($message);
        }

        foreach ($fixers as $fixer) {
            $fixerName = $fixer->getName();
            if (isset($rules[$fixerName]) && $fixer instanceof DeprecatedFixerInterface) {
                $successors = $fixer->getSuccessorsNames();
                $messageEnd = [] === $successors
                    ? \sprintf(' and will be removed in version %d.0.', Application::getMajorVersion() + 1)
                    : \sprintf('. Use %s instead.', str_replace('`', '"', Utils::naturalLanguageJoinWithBackticks($successors)));

                Future::triggerDeprecation(new \RuntimeException("Rule \"{$fixerName}\" is deprecated{$messageEnd}"));
            }
        }
    }

    /**
     * Apply path on config instance.
     *
     * @return iterable<\SplFileInfo>
     */
    private function resolveFinder(): iterable
    {
        $this->configFinderIsOverridden = false;

        if ($this->isStdIn()) {
            return new \ArrayIterator([new StdinFileInfo()]);
        }

        if (!\in_array(
            $this->options['path-mode'],
            self::PATH_MODE_VALUES,
            true,
        )) {
            throw new InvalidConfigurationException(\sprintf(
                'The path-mode "%s" is not defined, supported are %s.',
                $this->options['path-mode'],
                Utils::naturalLanguageJoin(self::PATH_MODE_VALUES),
            ));
        }

        $isIntersectionPathMode = self::PATH_MODE_INTERSECTION === $this->options['path-mode'];

        $paths = array_map(
            static fn (string $path): string => realpath($path), // @phpstan-ignore return.type
            $this->getPath(),
        );

        if (0 === \count($paths)) {
            if ($isIntersectionPathMode) {
                return new \ArrayIterator([]);
            }

            return $this->getConfig()->getFinder();
        }

        $pathsByType = [
            'file' => [],
            'dir' => [],
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $pathsByType['file'][] = $path;
            } else {
                $pathsByType['dir'][] = $path.\DIRECTORY_SEPARATOR;
            }
        }

        $nestedFinder = null;
        $currentFinder = $this->getConfig()->getFinder();

        try {
            $nestedFinder = $currentFinder instanceof \IteratorAggregate
                ? $currentFinder->getIterator()
                : (
                    $currentFinder instanceof \Traversable
                        ? $currentFinder
                        : new \ArrayIterator($currentFinder)
                );
        } catch (\Exception $e) {
        }

        if ($isIntersectionPathMode) {
            if (null === $nestedFinder) {
                throw new InvalidConfigurationException(
                    'Cannot create intersection with not-fully defined Finder in configuration file.',
                );
            }

            return new \CallbackFilterIterator(
                new \IteratorIterator($nestedFinder),
                static function (\SplFileInfo $current) use ($pathsByType): bool {
                    $currentRealPath = $current->getRealPath();

                    if (\in_array($currentRealPath, $pathsByType['file'], true)) {
                        return true;
                    }

                    foreach ($pathsByType['dir'] as $path) {
                        if (str_starts_with($currentRealPath, $path)) {
                            return true;
                        }
                    }

                    return false;
                },
            );
        }

        if (null !== $this->getConfigFile() && null !== $nestedFinder) {
            $this->configFinderIsOverridden = true;
        }

        if ($currentFinder instanceof SymfonyFinder && null === $nestedFinder) {
            // finder from configuration Symfony finder and it is not fully defined, we may fulfill it
            return $currentFinder->in($pathsByType['dir'])->append($pathsByType['file']);
        }

        return Finder::create()->in($pathsByType['dir'])->append($pathsByType['file']);
    }

    /**
     * Set option that will be resolved.
     *
     * @param mixed $value
     */
    private function setOption(string $name, $value): void
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(\sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * @param key-of<_Options> $optionName
     */
    private function resolveOptionBooleanValue(string $optionName): bool
    {
        $value = $this->options[$optionName];

        if (self::BOOL_YES === $value) {
            return true;
        }

        if (self::BOOL_NO === $value) {
            return false;
        }

        throw new InvalidConfigurationException(\sprintf('Expected "%s" or "%s" for option "%s", got "%s".', self::BOOL_YES, self::BOOL_NO, $optionName, \is_object($value) ? \get_class($value) : (\is_scalar($value) ? $value : \gettype($value))));
    }

    private static function separatedContextLessInclude(string $path): ConfigInterface
    {
        $config = include $path;

        // verify that the config has an instance of Config
        if (!$config instanceof ConfigInterface) {
            throw new InvalidConfigurationException(\sprintf('The config file: "%s" does not return a "%s" instance. Got: "%s".', $path, ConfigInterface::class, get_debug_type($config)));
        }

        return $config;
    }

    private function isCachingAllowedForRuntime(): bool
    {
        return $this->toolInfo->isInstalledAsPhar()
            || $this->toolInfo->isInstalledByComposer()
            || $this->toolInfo->isRunInsideDocker()
            || filter_var(getenv('PHP_CS_FIXER_ENFORCE_CACHE'), \FILTER_VALIDATE_BOOL);
    }
}
