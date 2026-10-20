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

use Clue\React\NDJson\Decoder;
use Clue\React\NDJson\Encoder;
use PhpCsFixer\Cache\NullCacheManager;
use PhpCsFixer\Config;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Runner\Event\FileProcessed;
use PhpCsFixer\Runner\Parallel\ParallelAction;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixer\Runner\Parallel\ParallelisationException;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\ToolInfoInterface;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;
use React\Socket\TcpConnector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'worker', description: 'Internal command for running fixers in parallel', hidden: true)]
final class WorkerCommand extends Command
{
    /** @var string Prefix used before JSON-encoded error printed in the worker's process */
    public const ERROR_PREFIX = 'WORKER_ERROR::';

    private ToolInfoInterface $toolInfo;
    private ConfigurationResolver $configurationResolver;
    private ErrorsManager $errorsManager;
    private EventDispatcherInterface $eventDispatcher;

    /** @var list<FileProcessed> */
    private array $events;

    public function __construct(ToolInfoInterface $toolInfo)
    {
        parent::__construct('worker');
        $this->setDescription('Internal command for running fixers in parallel');

        $this->setHidden(true);
        $this->toolInfo = $toolInfo;
        $this->errorsManager = new ErrorsManager();
        $this->eventDispatcher = new EventDispatcher();
    }

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputOption('port', null, InputOption::VALUE_REQUIRED, 'Specifies parallelisation server\'s port.'),
                new InputOption('identifier', null, InputOption::VALUE_REQUIRED, 'Specifies parallelisation process\' identifier.'),
                new InputOption('allow-risky', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Are risky fixers allowed (%s).', ConfigurationResolver::BOOL_VALUES), null, ConfigurationResolver::BOOL_VALUES),
                new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The path to a config file.'),
                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified.'),
                new InputOption('rules', '', InputOption::VALUE_REQUIRED, 'List of rules that should be run against configured paths.'),
                new InputOption('using-cache', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('Should cache be used (%s).', ConfigurationResolver::BOOL_VALUES), null, ConfigurationResolver::BOOL_VALUES),
                new InputOption('cache-file', '', InputOption::VALUE_REQUIRED, 'The path to the cache file.'),
                new InputOption('diff', '', InputOption::VALUE_NONE, 'Prints diff for each file.'),
                new InputOption('stop-on-violation', '', InputOption::VALUE_NONE, 'Stop execution on first violation.'),
            ],
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $identifier = $input->getOption('identifier');
        $port = $input->getOption('port');

        if (null === $identifier || !is_numeric($port)) {
            throw new ParallelisationException('Missing parallelisation options');
        }

        try {
            $runner = $this->createRunner($input);
        } catch (\Throwable $e) {
            throw new ParallelisationException('Unable to create runner: '.$e->getMessage(), 0, $e);
        }

        $loop = new StreamSelectLoop();
        $tcpConnector = new TcpConnector($loop);
        $tcpConnector
            ->connect(\sprintf('127.0.0.1:%d', $port))
            // @codeCoverageIgnoreStart
            ->then(
                function (ConnectionInterface $connection) use ($loop, $runner, $identifier): void {
                    $out = new Encoder($connection, \JSON_INVALID_UTF8_IGNORE);
                    $in = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_IGNORE);

                    // [REACT] Initialise connection with the parallelisation operator
                    $out->write(['action' => ParallelAction::WORKER_HELLO, 'identifier' => $identifier]);

                    $handleError = static function (\Throwable $error) use ($out): void {
                        $out->write([
                            'action' => ParallelAction::WORKER_ERROR_REPORT,
                            'class' => \get_class($error),
                            'message' => $error->getMessage(),
                            'file' => $error->getFile(),
                            'line' => $error->getLine(),
                            'code' => $error->getCode(),
                            'trace' => $error->getTraceAsString(),
                        ]);
                    };
                    $out->on('error', $handleError);
                    $in->on('error', $handleError);

                    // [REACT] Listen for messages from the parallelisation operator (analysis requests)
                    $in->on('data', function (array $json) use ($loop, $runner, $out): void {
                        \assert(isset($json['action']));

                        $action = $json['action'];

                        // Parallelisation operator does not have more to do, let's close the connection
                        if (ParallelAction::RUNNER_THANK_YOU === $action) {
                            // no payload to assert on

                            $loop->stop();

                            return;
                        }

                        if (ParallelAction::RUNNER_REQUEST_ANALYSIS !== $action) {
                            // At this point we only expect analysis requests, if any other action happen, we need to fix the code.
                            throw new \LogicException(\sprintf('Unexpected action ParallelAction::%s.', $action));
                        }

                        \assert(isset(
                            $json['files'],
                        ));

                        /** @var iterable<int, string> $files */
                        $files = $json['files'];

                        foreach ($files as $path) {
                            // Reset events because we want to collect only those coming from analysed files chunk
                            $this->events = [];
                            $runner->setFileIterator(new \ArrayIterator([new \SplFileInfo($path)]));
                            $analysisResult = $runner->fix();

                            if (1 !== \count($this->events)) {
                                throw new ParallelisationException('Runner did not report a fixing event or reported too many.');
                            }

                            if (1 < \count($analysisResult)) {
                                throw new ParallelisationException('Runner returned more analysis results than expected.');
                            }

                            $out->write([
                                'action' => ParallelAction::WORKER_RESULT,
                                'errors' => $this->errorsManager->forPath($path),
                                'file' => $path,
                                'fileHash' => $this->events[0]->getFileHash(),
                                'fixInfo' => array_pop($analysisResult),
                                'memoryUsage' => memory_get_peak_usage(true),
                                'status' => $this->events[0]->getStatus(),
                            ]);
                        }

                        // Request another file chunk (if available, the parallelisation operator will request new "run" action)
                        $out->write(['action' => ParallelAction::WORKER_GET_FILE_CHUNK]);
                    });
                },
                static function (\Throwable $error) use ($errorOutput): void {
                    // @TODO Verify onRejected behaviour → https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/7777#discussion_r1590399285
                    $errorOutput->writeln($error->getMessage());
                },
            )
            // @codeCoverageIgnoreEnd
        ;

        $loop->run();

        return Command::SUCCESS;
    }

    private function createRunner(InputInterface $input): Runner
    {
        $passedConfig = $input->getOption('config');
        $passedRules = $input->getOption('rules');

        if (null !== $passedConfig && ConfigurationResolver::IGNORE_CONFIG_FILE !== $passedConfig && null !== $passedRules) {
            throw new \RuntimeException('Passing both `--config` and `--rules` options is not allowed');
        }

        // There's no one single source of truth when it comes to fixing single file, we need to collect statuses from events.
        $this->eventDispatcher->addListener(FileProcessed::NAME, function (FileProcessed $event): void {
            $this->events[] = $event;
        });

        $this->configurationResolver = new ConfigurationResolver(
            new Config(),
            [
                'allow-risky' => $input->getOption('allow-risky'),
                'config' => $passedConfig,
                'dry-run' => $input->getOption('dry-run'),
                'rules' => $passedRules,
                'path' => [],
                'path-mode' => ConfigurationResolver::PATH_MODE_OVERRIDE, // IMPORTANT! WorkerCommand is called with file that already passed filtering, so here we can rely on PATH_MODE_OVERRIDE.
                'using-cache' => $input->getOption('using-cache'),
                'cache-file' => $input->getOption('cache-file'),
                'diff' => $input->getOption('diff'),
                'stop-on-violation' => $input->getOption('stop-on-violation'),
            ],
            getcwd(), // @phpstan-ignore argument.type
            $this->toolInfo,
        );

        return new Runner(
            null, // Paths are known when parallelisation server requests new chunk, not now
            $this->configurationResolver->getFixers(),
            $this->configurationResolver->getDiffer(),
            $this->eventDispatcher,
            $this->errorsManager,
            $this->configurationResolver->getLinter(),
            $this->configurationResolver->isDryRun(),
            new NullCacheManager(), // IMPORTANT! We pass null cache, as cache is read&write in main process and we do not need to do it again.
            $this->configurationResolver->getDirectory(),
            $this->configurationResolver->shouldStopOnViolation(),
            ParallelConfigFactory::sequential(), // IMPORTANT! Worker must run in sequential mode.
            null,
            $this->configurationResolver->getConfigFile(),
            $this->configurationResolver->getRuleCustomisationPolicy(),
        );
    }
}
