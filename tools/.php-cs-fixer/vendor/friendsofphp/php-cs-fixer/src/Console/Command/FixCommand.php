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

namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Console\Application;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Console\Output\ErrorOutput;
use PhpCsFixer\Console\Output\OutputContext;
use PhpCsFixer\Console\Output\Progress\ProgressOutputFactory;
use PhpCsFixer\Console\Output\Progress\ProgressOutputType;
use PhpCsFixer\Console\Report\FixReport\ReporterFactory;
use PhpCsFixer\Console\Report\FixReport\ReportSummary;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSets;
use PhpCsFixer\Runner\Event\FileProcessed;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\ToolInfoInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @final
 *
 * @TODO 4.0: mark as final
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'fix', description: 'Fixes a directory or a file.')]
/* final */ class FixCommand extends Command
{
    private EventDispatcherInterface $eventDispatcher;

    private ErrorsManager $errorsManager;

    private Stopwatch $stopwatch;

    private ConfigInterface $defaultConfig;

    private ToolInfoInterface $toolInfo;

    private ProgressOutputFactory $progressOutputFactory;

    public function __construct(ToolInfoInterface $toolInfo)
    {
        parent::__construct('fix');
        $this->setDescription('Fixes a directory or a file.');

        $this->eventDispatcher = new EventDispatcher();
        $this->errorsManager = new ErrorsManager();
        $this->stopwatch = new Stopwatch();
        $this->defaultConfig = new Config();
        $this->toolInfo = $toolInfo;
        $this->progressOutputFactory = new ProgressOutputFactory();
    }

    /**
     * {@inheritdoc}
     *
     * Override here to only generate the help copy when used.
     */
    public function getHelp(): string
    {
        return <<<'EOF'
            The <info>%command.name%</info> command executes a %command.name% of coding standards problems:

                <info>$ php %command.full_name</info>

            You can also specify a path to execute a command only over a nested directory or a file.
            By default <comment>--path-mode</comment> is set to `override`, which means, that if you specify the path to a directory or a file via
            command arguments, then the paths provided to a `Finder` in config file will be ignored. You can also use <comment>--path-mode=intersection</comment>,
            which will use the intersection of the paths from the config file and from the argument:

                <info>$ php %command.full_name% --path-mode=intersection /path/to/dir_or_file</info>

            The <comment>--format</comment> option for the output format. Supported formats are `@auto` (default one on v4+), `txt` (default one on v3), `json`, `xml`, `checkstyle`, `junit` and `gitlab`.

            * `@auto` aims to auto-select best reporter for given CI or local execution (resolution into best format is outside of BC promise and is future-ready)
              * `gitlab` for GitLab
            * `@auto,{format}` takes `@auto` under CI, and {format} otherwise

            NOTE: the output for the following formats are generated in accordance with schemas

            * `checkstyle` follows the common `"checkstyle" XML schema </doc/schemas/fix/checkstyle.xsd>`_
            * `gitlab` follows the `codeclimate JSON schema </doc/schemas/fix/codeclimate.json>`_
            * `json` follows the `own JSON schema </doc/schemas/fix/schema.json>`_
            * `junit` follows the `JUnit XML schema from Jenkins </doc/schemas/fix/junit-10.xsd>`_
            * `xml` follows the `own XML schema </doc/schemas/fix/xml.xsd>`_

            The <comment>--quiet</comment> Do not output any message.

            The <comment>--verbose</comment> option will show the applied rules. When using the `txt` format it will also display progress notifications.

            NOTE: if there is an error like "errors reported during linting after fixing", you can use this to be even more verbose for debugging purpose

            * `-v`: verbose
            * `-vv`: very verbose
            * `-vvv`: debug

            EOF. /* @TODO: 4.0 - change to @PER */ <<<'EOF'

            The <comment>--rules</comment> option allows to explicitly select rules to use,
            overriding the default PSR-12 or your own project config:

                <info>$ php %command.full_name% . --rules=line_ending,full_opening_tag,indentation_type</info>

            You can also exclude the rules you don't want by placing a dash in front of the rule name, like <comment>-name_of_fixer</comment>.

                <info>$ php %command.full_name% . --rules=@Symfony,-@PSR1,-blank_line_before_statement,strict_comparison</info>

            Complete configuration for rules can be supplied using a `json` formatted string as well.

                <info>$ php %command.full_name% . --rules='{"concat_space": {"spacing": "none"}}'</info>

            The <comment>--dry-run</comment> flag will run the fixer without making changes to your files.

            The <comment>--sequential</comment> flag will enforce sequential analysis even if parallel config is provided.

            The <comment>--diff</comment> flag can be used to let the fixer output all the changes it makes.

            The <comment>--allow-risky</comment> option (pass `yes` or `no`) allows you to set whether risky rules may run. Default value is taken from config file.
            A rule is considered risky if it could change code behaviour. By default no risky rules are run.

            The <comment>--stop-on-violation</comment> flag stops the execution upon first file that needs to be fixed.

            The <comment>--show-progress</comment> option allows you to choose the way process progress is rendered:

            * <comment>none</comment>: disables progress output;
            * <comment>dots</comment>: multiline progress output with number of files and percentage on each line.
            * <comment>bar</comment>: single line progress output with number of files and calculated percentage.

            If the option is not provided, it defaults to <comment>bar</comment> unless a config file that disables output is used, in which case it defaults to <comment>none</comment>. This option has no effect if the verbosity of the command is less than <comment>verbose</comment>.

                <info>$ php %command.full_name% --verbose --show-progress=dots</info>

            By using <comment>--using-cache</comment> option with `yes` or `no` you can set if the caching
            mechanism should be used.

            The command can also read from standard input, in which case it won't
            automatically fix anything:

                <info>$ cat foo.php | php %command.full_name% --diff -</info>

            Finally, if you don't need BC kept on CLI level, you might use `PHP_CS_FIXER_FUTURE_MODE` to start using options that
            would be default in next MAJOR release and to forbid using deprecated configuration:

                <info>$ PHP_CS_FIXER_FUTURE_MODE=1 php %command.full_name% -v --diff</info>

            Exit code
            ---------

            Exit code of the `%command.name%` command is built using following bit flags:

            *  0 - OK.
            *  1 - General error (or PHP minimal requirement not matched).
            *  4 - Some files have invalid syntax (only in dry-run mode).
            *  8 - Some files need fixing (only in dry-run mode).
            * 16 - Configuration error of the application.
            * 32 - Configuration error of a Fixer.
            * 64 - Exception raised within the application.

            EOF;
    }

    protected function configure(): void
    {
        $reporterFactory = new ReporterFactory();
        $reporterFactory->registerBuiltInReporters();
        $formats = $reporterFactory->getFormats();
        array_unshift($formats, '@auto', '@auto,txt');

        $progressOutputTypes = ProgressOutputType::all();

        $this->setDefinition(
            [
                new InputArgument('path', InputArgument::IS_ARRAY, 'The path(s) that rules will be run against (each path can be a file or directory).'),
                new InputOption('path-mode', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Specify path mode (%s).', ConfigurationResolver::PATH_MODE_VALUES), ConfigurationResolver::PATH_MODE_OVERRIDE, ConfigurationResolver::PATH_MODE_VALUES),
                new InputOption('allow-risky', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Are risky fixers allowed (%s).', ConfigurationResolver::BOOL_VALUES), null, ConfigurationResolver::BOOL_VALUES),
                new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The path to a config file.'),
                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified.'),
                new InputOption('rules', '', InputOption::VALUE_REQUIRED, 'List of rules that should be run against configured paths.', null, static function () {
                    $fixerFactory = new FixerFactory();
                    $fixerFactory->registerBuiltInFixers();
                    $fixers = array_map(static fn (FixerInterface $fixer) => $fixer->getName(), $fixerFactory->getFixers());

                    return array_merge(RuleSets::getSetDefinitionNames(), $fixers);
                }),
                new InputOption('using-cache', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Should cache be used (%s).', ConfigurationResolver::BOOL_VALUES), null, ConfigurationResolver::BOOL_VALUES),
                new InputOption('allow-unsupported-php-version', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Should the command refuse to run on unsupported PHP version (%s).', ConfigurationResolver::BOOL_VALUES), null, ConfigurationResolver::BOOL_VALUES),
                new InputOption('cache-file', '', InputOption::VALUE_REQUIRED, 'The path to the cache file.'),
                new InputOption('diff', '', InputOption::VALUE_NONE, 'Prints diff for each file.'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('To output results in other formats (%s).', $formats), null, $formats),
                new InputOption('stop-on-violation', '', InputOption::VALUE_NONE, 'Stop execution on first violation.'),
                new InputOption('show-progress', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Type of progress indicator (%s).', $progressOutputTypes), null, $progressOutputTypes),
                new InputOption('sequential', '', InputOption::VALUE_NONE, 'Enforce sequential analysis.'),
            ],
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbosity = $output->getVerbosity();

        $passedConfig = $input->getOption('config');
        $passedRules = $input->getOption('rules');

        if (null !== $passedConfig && ConfigurationResolver::IGNORE_CONFIG_FILE !== $passedConfig && null !== $passedRules) {
            throw new InvalidConfigurationException('Passing both `--config` and `--rules` options is not allowed.');
        }

        $resolver = new ConfigurationResolver(
            $this->defaultConfig,
            [
                'allow-risky' => $input->getOption('allow-risky'),
                'config' => $passedConfig,
                'dry-run' => $this->isDryRun($input),
                'rules' => $passedRules,
                'path' => $input->getArgument('path'),
                'path-mode' => $input->getOption('path-mode'),
                'using-cache' => $input->getOption('using-cache'),
                'allow-unsupported-php-version' => $input->getOption('allow-unsupported-php-version'),
                'cache-file' => $input->getOption('cache-file'),
                'format' => $input->getOption('format'),
                'diff' => $input->getOption('diff'),
                'stop-on-violation' => $input->getOption('stop-on-violation'),
                'verbosity' => $verbosity,
                'show-progress' => $input->getOption('show-progress'),
                'sequential' => $input->getOption('sequential'),
            ],
            getcwd(), // @phpstan-ignore argument.type
            $this->toolInfo,
        );

        $reporter = $resolver->getReporter();

        $stdErr = $output instanceof ConsoleOutputInterface
            ? $output->getErrorOutput()
            : ('txt' === $reporter->getFormat() ? $output : null);

        if (null !== $stdErr) {
            $stdErr->writeln(Application::getAboutWithRuntime(true));

            if (version_compare(\PHP_VERSION, ConfigInterface::PHP_VERSION_SYNTAX_SUPPORTED.'.99', '>')) {
                $message = \sprintf(
                    'PHP CS Fixer currently supports PHP syntax only up to PHP %s, current PHP version: %s.',
                    ConfigInterface::PHP_VERSION_SYNTAX_SUPPORTED,
                    \PHP_VERSION,
                );

                if (!$resolver->getUnsupportedPhpVersionAllowed()) {
                    $message .= ' Add `Config::setUnsupportedPhpVersionAllowed(true)` to allow executions on unsupported PHP versions. Such execution may be unstable and you may experience code modified in a wrong way.';
                    $stdErr->writeln(\sprintf(
                        $stdErr->isDecorated() ? '<bg=red;fg=white;>%s</>' : '%s',
                        $message,
                    ));

                    return 1;
                }
                $message .= ' Execution may be unstable. You may experience code modified in a wrong way. Please report such cases at https://github.com/PHP-CS-Fixer/PHP-CS-Fixer. Remove Config::setUnsupportedPhpVersionAllowed(true) to allow executions only on supported PHP versions.';
                $stdErr->writeln(\sprintf(
                    $stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s',
                    $message,
                ));
            }

            $configFile = $resolver->getConfigFile();
            $stdErr->writeln(\sprintf('Loaded config <comment>%s</comment>%s.', $resolver->getConfig()->getName(), null === $configFile ? '' : ' from "'.$configFile.'"'));

            if (null === $configFile && ConfigurationResolver::IGNORE_CONFIG_FILE !== $passedConfig && null === $passedRules) {
                if (false === $input->isInteractive()) {
                    $stdErr->writeln(
                        \sprintf(
                            $stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s',
                            'No config file found. Please create one using `php-cs-fixer init`.',
                        ),
                    );
                } else {
                    $io = new SymfonyStyle($input, $stdErr);
                    $shallCreateConfigFile = 'yes' === $io->choice(
                        'Do you want to create the config file?',
                        ['yes', 'no'],
                        'yes',
                    );
                    if ($shallCreateConfigFile) {
                        $returnCode = $this->getApplication()->doRun(
                            new ArrayInput([
                                'command' => 'init',
                            ]),
                            $output,
                        );
                        $stdErr->writeln('Config file created, re-run the command to put it in action.');

                        return $returnCode;
                    }
                }
            }

            $isParallel = $resolver->getParallelConfig()->getMaxProcesses() > 1;

            $stdErr->writeln(\sprintf(
                'Running analysis on %d core%s.',
                $resolver->getParallelConfig()->getMaxProcesses(),
                $isParallel ? \sprintf(
                    's with %d file%s per process',
                    $resolver->getParallelConfig()->getFilesPerProcess(),
                    $resolver->getParallelConfig()->getFilesPerProcess() > 1 ? 's' : '',
                ) : ' sequentially',
            ));

            if ($resolver->getUsingCache()) {
                $cacheFile = $resolver->getCacheFile();

                if (is_file($cacheFile)) {
                    $stdErr->writeln(\sprintf('Using cache file "%s".', $cacheFile));
                }
            }
        }

        $finder = new \ArrayIterator(array_filter(
            iterator_to_array($resolver->getFinder()),
            static fn (\SplFileInfo $fileInfo) => false !== $fileInfo->getRealPath(),
        ));

        if (null !== $stdErr) {
            if ($resolver->configFinderIsOverridden()) {
                $stdErr->writeln(
                    \sprintf($stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s', 'Paths from configuration have been overridden by paths provided as command arguments.'),
                );
            }

            if ($resolver->configRulesAreOverridden()) {
                $stdErr->writeln(
                    \sprintf($stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s', 'Rules from configuration have been overridden by rules provided as command argument.'),
                );
            }
        }

        $progressType = $resolver->getProgressType();
        $progressOutput = $this->progressOutputFactory->create(
            $progressType,
            new OutputContext(
                $stdErr,
                (new Terminal())->getWidth(),
                \count($finder),
            ),
        );

        $runner = new Runner(
            $finder,
            $resolver->getFixers(),
            $resolver->getDiffer(),
            ProgressOutputType::NONE !== $progressType ? $this->eventDispatcher : null,
            $this->errorsManager,
            $resolver->getLinter(),
            $resolver->isDryRun(),
            $resolver->getCacheManager(),
            $resolver->getDirectory(),
            $resolver->shouldStopOnViolation(),
            $resolver->getParallelConfig(),
            $input,
            $resolver->getConfigFile(),
            $resolver->getRuleCustomisationPolicy(),
        );

        $this->eventDispatcher->addListener(FileProcessed::NAME, [$progressOutput, 'onFixerFileProcessed']);
        $this->stopwatch->start('fixFiles');
        $changed = $runner->fix();
        $this->stopwatch->stop('fixFiles');
        $this->eventDispatcher->removeListener(FileProcessed::NAME, [$progressOutput, 'onFixerFileProcessed']);

        $progressOutput->printLegend();

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $reportSummary = new ReportSummary(
            $changed,
            \count($finder),
            (int) $fixEvent->getDuration(), // ignore microseconds fraction
            memory_get_peak_usage(true) + $runner->getWorkersMemoryUsage(),
            OutputInterface::VERBOSITY_VERBOSE <= $verbosity,
            $resolver->isDryRun(),
            $output->isDecorated(),
        );

        $output->isDecorated()
            ? $output->write($reporter->generate($reportSummary))
            : $output->write($reporter->generate($reportSummary), false, OutputInterface::OUTPUT_RAW);

        $invalidErrors = $this->errorsManager->getInvalidErrors();
        $exceptionErrors = $this->errorsManager->getExceptionErrors();
        $lintErrors = $this->errorsManager->getLintErrors();

        if (null !== $stdErr) {
            $errorOutput = new ErrorOutput($stdErr);

            if (\count($invalidErrors) > 0) {
                $errorOutput->listErrors('linting before fixing', $invalidErrors);
            }

            if (\count($exceptionErrors) > 0) {
                $errorOutput->listErrors('fixing', $exceptionErrors);
                if ($isParallel) {
                    $stdErr->writeln('To see details of the error(s), re-run the command with `--sequential -vvv [file]`');
                }
            }

            if (\count($lintErrors) > 0) {
                $errorOutput->listErrors('linting after fixing', $lintErrors);
            }
        }

        $exitStatusCalculator = new FixCommandExitStatusCalculator();

        return $exitStatusCalculator->calculate(
            $resolver->isDryRun(),
            \count($changed) > 0,
            \count($invalidErrors) > 0,
            \count($exceptionErrors) > 0,
            \count($lintErrors) > 0,
        );
    }

    protected function isDryRun(InputInterface $input): bool
    {
        return $input->getOption('dry-run'); // @phpstan-ignore symfonyConsole.optionNotFound (Because PHPStan doesn't recognise the method is overridden in the child class and this parameter is _not_ used in the child class.)
    }
}
