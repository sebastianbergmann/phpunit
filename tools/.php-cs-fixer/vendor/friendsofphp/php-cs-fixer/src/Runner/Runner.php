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

namespace PhpCsFixer\Runner;

use Clue\React\NDJson\Decoder;
use Clue\React\NDJson\Encoder;
use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\Cache\Directory;
use PhpCsFixer\Cache\DirectoryInterface;
use PhpCsFixer\Config\NullRuleCustomisationPolicy;
use PhpCsFixer\Config\RuleCustomisationPolicyInterface;
use PhpCsFixer\Console\Command\WorkerCommand;
use PhpCsFixer\Differ\DifferInterface;
use PhpCsFixer\Error\Error;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Error\SourceExceptionFactory;
use PhpCsFixer\FileReader;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Future;
use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Linter\LintingException;
use PhpCsFixer\Linter\LintingResultInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Runner\Event\AnalysisStarted;
use PhpCsFixer\Runner\Event\FileProcessed;
use PhpCsFixer\Runner\Parallel\ParallelAction;
use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixer\Runner\Parallel\ParallelisationException;
use PhpCsFixer\Runner\Parallel\ProcessFactory;
use PhpCsFixer\Runner\Parallel\ProcessIdentifier;
use PhpCsFixer\Runner\Parallel\ProcessPool;
use PhpCsFixer\Runner\Parallel\WorkerException;
use PhpCsFixer\Tokenizer\Analyzer\FixerAnnotationAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @phpstan-type _RunResult array<string, array{appliedFixers: list<string>, diff: string}>
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Greg Korba <greg@codito.dev>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @TODO v4: decide if marking Runner as internal or making it dependencies public
 *
 * @phpstan-ignore-next-line phpCsFixer.internalTypeInPublicApi
 */
final class Runner
{
    /**
     * Buffer size used in the NDJSON decoder for communication between main process and workers.
     *
     * @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/8068
     */
    private const PARALLEL_BUFFER_SIZE = 16 * (1_024 * 1_024 /* 1MB */);

    private DifferInterface $differ;

    private DirectoryInterface $directory;

    private ?EventDispatcherInterface $eventDispatcher;

    private ErrorsManager $errorsManager;

    private CacheManagerInterface $cacheManager;

    private bool $isDryRun;

    private LinterInterface $linter;

    /**
     * @var null|\Traversable<array-key, \SplFileInfo>
     */
    private ?\Traversable $fileIterator = null;

    private int $fileCount;

    /**
     * @var array<string, int> key is process identifier, value is memory usage in bytes
     */
    private array $workersMemoryUsageByProcess = [];

    /**
     * @var list<FixerInterface>
     */
    private array $fixers;

    /**
     * @var array<non-empty-string, FixerInterface>
     */
    private array $fixersByName;

    private bool $stopOnViolation;

    private ParallelConfig $parallelConfig;

    private ?InputInterface $input;

    private ?string $configFile;

    private RuleCustomisationPolicyInterface $ruleCustomisationPolicy;

    /**
     * @param null|\Traversable<array-key, \SplFileInfo> $fileIterator
     * @param list<FixerInterface>                       $fixers
     */
    public function __construct(
        ?\Traversable $fileIterator,
        array $fixers,
        DifferInterface $differ,
        ?EventDispatcherInterface $eventDispatcher,
        ErrorsManager $errorsManager,
        LinterInterface $linter,
        bool $isDryRun,
        CacheManagerInterface $cacheManager,
        ?DirectoryInterface $directory = null,
        bool $stopOnViolation = false,
        // @TODO Make these arguments required in 4.0
        ?ParallelConfig $parallelConfig = null,
        ?InputInterface $input = null,
        ?string $configFile = null,
        ?RuleCustomisationPolicyInterface $ruleCustomisationPolicy = null
    ) {
        // Required only for main process (calculating workers count)
        $this->fileCount = null !== $fileIterator ? \count(iterator_to_array($fileIterator)) : 0;

        $this->fileIterator = $fileIterator;
        $this->fixers = $fixers;
        $this->fixersByName = array_reduce(
            $fixers,
            static function (array $carry, FixerInterface $fixer): array {
                $carry[$fixer->getName()] = $fixer;

                return $carry;
            },
            [],
        );
        $this->differ = $differ;
        $this->eventDispatcher = $eventDispatcher;
        $this->errorsManager = $errorsManager;
        $this->linter = $linter;
        $this->isDryRun = $isDryRun;
        $this->cacheManager = $cacheManager;
        $this->directory = $directory ?? new Directory('');
        $this->stopOnViolation = $stopOnViolation;
        $this->parallelConfig = $parallelConfig ?? ParallelConfigFactory::detect();
        $this->input = $input;
        $this->configFile = $configFile;
        $this->ruleCustomisationPolicy = $ruleCustomisationPolicy ?? new NullRuleCustomisationPolicy();
    }

    /**
     * Total workers memory. 0 if not run in parallel mode.
     */
    public function getWorkersMemoryUsage(): int
    {
        return array_sum($this->workersMemoryUsageByProcess);
    }

    /**
     * @param \Traversable<array-key, \SplFileInfo> $fileIterator
     *
     * @TODO v4: mark internal
     * @TODO consider to drop this method and make iterator parameter obligatory in constructor,
     *       more in https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/7777/files#r1590447581
     */
    public function setFileIterator(iterable $fileIterator): void
    {
        $this->fileIterator = $fileIterator instanceof \Traversable ? $fileIterator : new \ArrayIterator($fileIterator);

        // Required only for main process (calculating workers count)
        $this->fileCount = \count(iterator_to_array($fileIterator));
    }

    /**
     * @return _RunResult
     */
    public function fix(): array
    {
        if (0 === $this->fileCount) {
            return [];
        }

        $ruleCustomisers = $this->ruleCustomisationPolicy->getRuleCustomisers();
        $this->validateRulesNamesForExceptions(
            array_keys($ruleCustomisers),
            <<<'EOT'
                Rule Customisation Policy contains customisers for rules that are not in the current set of enabled rules:
                %s

                Please check your configuration to ensure that these rules are included, or update your Rule Customisation Policy if they have been replaced by other rules in the version of PHP CS Fixer you are using.
                EOT,
        );

        // @TODO 4.0: Remove condition and its body, as no longer needed when param will be required in the constructor.
        // This is a fallback only in case someone calls `new Runner()` in a custom repo and does not provide v4-ready params in v3-codebase.
        if (null === $this->input) {
            return $this->fixSequential();
        }

        if (
            1 === $this->parallelConfig->getMaxProcesses()
            || $this->fileCount <= $this->parallelConfig->getFilesPerProcess()
        ) {
            return $this->fixSequential();
        }

        return $this->fixParallel();
    }

    /**
     * @param list<string>     $ruleExceptions
     * @param non-empty-string $errorTemplate
     */
    private function validateRulesNamesForExceptions(array $ruleExceptions, string $errorTemplate): void
    {
        if (true === filter_var(getenv('PHP_CS_FIXER_IGNORE_MISMATCHED_RULES_EXCEPTIONS'), \FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        if ([] === $ruleExceptions) {
            return;
        }

        $fixersByName = $this->fixersByName;
        $usedRules = array_keys($fixersByName);
        $missingRuleNames = array_diff($ruleExceptions, $usedRules);

        if ([] === $missingRuleNames) {
            return;
        }

        /** @TODO v3.999 check if rule is deprecated and show the replacement rules as well */
        $missingRulesDesc = implode("\n", array_map(
            static function (string $name) use ($fixersByName): string {
                $extra = '';
                if ('' === $name) {
                    $extra = '(no name provided)';
                } elseif ('@' === $name[0]) {
                    $extra = ' (can exclude only rules, not sets)';
                } elseif (!isset($fixersByName[$name])) {
                    $extra = ' (unknown rule)';
                }

                return '- '.$name.$extra;
            },
            $missingRuleNames,
        ));

        throw new \RuntimeException(
            \sprintf($errorTemplate, $missingRulesDesc),
        );
    }

    /**
     * Heavily inspired by {@see https://github.com/phpstan/phpstan-src/blob/9ce425bca5337039fb52c0acf96a20a2b8ace490/src/Parallel/ParallelAnalyser.php}.
     *
     * @return _RunResult
     */
    private function fixParallel(): array
    {
        $this->dispatchEvent(AnalysisStarted::NAME, new AnalysisStarted(AnalysisStarted::MODE_PARALLEL, $this->isDryRun));

        $changed = [];
        $streamSelectLoop = new StreamSelectLoop();
        $server = new TcpServer('127.0.0.1:0', $streamSelectLoop);
        $serverPort = parse_url($server->getAddress() ?? '', \PHP_URL_PORT);

        if (!is_numeric($serverPort)) {
            throw new ParallelisationException(\sprintf(
                'Unable to parse server port from "%s"',
                $server->getAddress() ?? '',
            ));
        }

        $processPool = new ProcessPool(
            $server,
            static function () use ($streamSelectLoop): void {
                $streamSelectLoop->stop();
            },
        );
        $maxFilesPerProcess = $this->parallelConfig->getFilesPerProcess();
        $fileIterator = $this->getFilteringFileIterator();
        $fileIterator->rewind();

        $getFileChunk = static function () use ($fileIterator, $maxFilesPerProcess): array {
            $files = [];

            while (\count($files) < $maxFilesPerProcess) {
                $current = $fileIterator->current();

                if (null === $current) {
                    break;
                }

                $files[] = $current->getPathname();

                $fileIterator->next();
            }

            return $files;
        };

        // [REACT] Handle worker's handshake (init connection)
        $server->on('connection', static function (ConnectionInterface $connection) use ($processPool, $getFileChunk): void {
            $decoder = new Decoder(
                $connection,
                true,
                512,
                \JSON_INVALID_UTF8_IGNORE,
                self::PARALLEL_BUFFER_SIZE,
            );
            $encoder = new Encoder($connection, \JSON_INVALID_UTF8_IGNORE);

            // [REACT] Bind connection when worker's process requests "hello" action (enables 2-way communication)
            $decoder->on('data', static function (array $data) use ($processPool, $getFileChunk, $decoder, $encoder): void {
                \assert(isset($data['action']));

                if (ParallelAction::WORKER_HELLO !== $data['action']) {
                    return;
                }

                \assert(isset(
                    $data['identifier'],
                ));

                $identifier = ProcessIdentifier::fromRaw($data['identifier']);

                // Avoid race condition where worker tries to establish connection,
                // but runner already ended all processes because `stop-on-violation` mode was enabled.
                try {
                    $process = $processPool->getProcess($identifier);
                } catch (ParallelisationException $e) {
                    return;
                }

                $process->bindConnection($decoder, $encoder);
                $fileChunk = $getFileChunk();

                if (0 === \count($fileChunk)) {
                    // @codeCoverageIgnoreStart
                    $process->request(['action' => ParallelAction::RUNNER_THANK_YOU]);
                    $processPool->endProcessIfKnown($identifier);

                    return;
                    // @codeCoverageIgnoreEnd
                }

                $process->request(['action' => ParallelAction::RUNNER_REQUEST_ANALYSIS, 'files' => $fileChunk]);
            });
        });

        $processesToSpawn = min(
            $this->parallelConfig->getMaxProcesses(),
            max(
                1,
                (int) ceil($this->fileCount / $this->parallelConfig->getFilesPerProcess()),
            ),
        );
        $processFactory = new ProcessFactory();

        for ($i = 0; $i < $processesToSpawn; ++$i) {
            $identifier = ProcessIdentifier::create();
            $process = $processFactory->create(
                $streamSelectLoop,
                $this->input,
                new RunnerConfig(
                    $this->isDryRun,
                    $this->stopOnViolation,
                    $this->parallelConfig,
                    $this->configFile,
                ),
                $identifier,
                $serverPort,
            );
            $processPool->addProcess($identifier, $process);
            $process->start(
                // [REACT] Handle workers' responses (multiple actions possible)
                function (array $workerResponse) use ($processPool, $process, $identifier, $getFileChunk, &$changed): void {
                    \assert(isset($workerResponse['action']));

                    // File analysis result (we want close-to-realtime progress with frequent cache savings)
                    if (ParallelAction::WORKER_RESULT === $workerResponse['action']) {
                        \assert(isset(
                            $workerResponse['errors'],
                            $workerResponse['file'],
                            // $workerResponse['fileHash'], // optional
                            // $workerResponse['fixInfo'], // optional
                            $workerResponse['memoryUsage'],
                            $workerResponse['status'],
                        ));

                        // Dispatch an event for each file processed and dispatch its status (required for progress output)
                        $this->dispatchEvent(FileProcessed::NAME, new FileProcessed($workerResponse['status']));

                        if (isset($workerResponse['fileHash'])) {
                            $this->cacheManager->setFileHash($workerResponse['file'], $workerResponse['fileHash']);
                        }

                        foreach ($workerResponse['errors'] as $error) {
                            $this->errorsManager->report(new Error(
                                $error['type'],
                                $error['filePath'],
                                null !== $error['source']
                                    ? SourceExceptionFactory::fromArray($error['source'])
                                    : null,
                                $error['appliedFixers'],
                                $error['diff'],
                            ));
                        }

                        // we collect memory on each file, as any violation may terminate processPool via stopOnViolation
                        $this->workersMemoryUsageByProcess[$identifier->toString()] = $workerResponse['memoryUsage'];

                        // Pass-back information about applied changes (only if there are any)
                        if (isset($workerResponse['fixInfo'])) {
                            $relativePath = $this->directory->getRelativePathTo($workerResponse['file']);
                            $changed[$relativePath] = $workerResponse['fixInfo'];

                            if ($this->stopOnViolation) {
                                $processPool->endAll();

                                return;
                            }
                        }

                        return;
                    }

                    if (ParallelAction::WORKER_GET_FILE_CHUNK === $workerResponse['action']) {
                        // no payload to assert on

                        // Request another chunk of files, if still available
                        $fileChunk = $getFileChunk();

                        if (0 === \count($fileChunk)) {
                            $process->request(['action' => ParallelAction::RUNNER_THANK_YOU]);
                            $processPool->endProcessIfKnown($identifier);

                            return;
                        }

                        // @codeCoverageIgnoreStart
                        $process->request(['action' => ParallelAction::RUNNER_REQUEST_ANALYSIS, 'files' => $fileChunk]);

                        return;
                        // @codeCoverageIgnoreEnd
                    }

                    if (ParallelAction::WORKER_ERROR_REPORT === $workerResponse['action']) {
                        \assert(isset(
                            $workerResponse['class'],
                            $workerResponse['message'],
                            $workerResponse['file'],
                            $workerResponse['line'],
                            $workerResponse['code'],
                            $workerResponse['trace'],
                        ));

                        throw WorkerException::fromRaw($workerResponse);
                    }

                    throw new ParallelisationException('Unsupported action: '.($workerResponse['action'] ?? 'n/a'));
                },

                // [REACT] Handle errors encountered during worker's execution
                static function (\Throwable $error) use ($processPool): void {
                    $processPool->endAll();

                    throw new ParallelisationException($error->getMessage(), $error->getCode(), $error);
                },

                // [REACT] Handle worker's shutdown
                static function ($exitCode, string $output) use ($processPool, $identifier): void {
                    // @codeCoverageIgnoreStart
                    $processPool->endProcessIfKnown($identifier);

                    if (0 === $exitCode || null === $exitCode) {
                        return;
                    }

                    $errorsReported = Preg::matchAll(
                        \sprintf('/^(?:%s)([^\n]+)+/m', WorkerCommand::ERROR_PREFIX),
                        $output,
                        $matches,
                    );

                    if ($errorsReported > 0) {
                        throw WorkerException::fromRaw(
                            json_decode($matches[1][0], true, 512, \JSON_THROW_ON_ERROR),
                        );
                    }
                    // @codeCoverageIgnoreEnd
                },
            );
        }

        $streamSelectLoop->run();

        return $changed;
    }

    /**
     * @return _RunResult
     */
    private function fixSequential(): array
    {
        $this->dispatchEvent(AnalysisStarted::NAME, new AnalysisStarted(AnalysisStarted::MODE_SEQUENTIAL, $this->isDryRun));

        $changed = [];
        $collection = $this->getLintingFileIterator();

        foreach ($collection as $file) {
            $fixInfo = $this->fixFile($file, $collection->currentLintingResult());

            // we do not need Tokens to still caching just fixed file - so clear the cache
            Tokens::clearCache();

            if (null !== $fixInfo) {
                $relativePath = $this->directory->getRelativePathTo($file->__toString());
                $changed[$relativePath] = $fixInfo;

                if ($this->stopOnViolation) {
                    break;
                }
            }
        }

        return $changed;
    }

    /**
     * @return null|array{appliedFixers: list<string>, diff: string}
     */
    private function fixFile(\SplFileInfo $file, LintingResultInterface $lintingResult): ?array
    {
        $filePathname = $file->getPathname();

        try {
            $lintingResult->check();
        } catch (LintingException $e) {
            $this->dispatchEvent(
                FileProcessed::NAME,
                new FileProcessed(FileProcessed::STATUS_INVALID),
            );

            $this->errorsManager->report(new Error(Error::TYPE_INVALID, $filePathname, $e));

            return null;
        }

        $old = FileReader::createSingleton()->read($file->getRealPath());

        $tokens = Tokens::fromCode($old);

        if (
            Future::isFutureModeEnabled() // @TODO 4.0 drop this line
            && !filter_var(getenv('PHP_CS_FIXER_NON_MONOLITHIC'), \FILTER_VALIDATE_BOOL)
            && !$tokens->isMonolithicPhp()
        ) {
            $this->dispatchEvent(
                FileProcessed::NAME,
                new FileProcessed(FileProcessed::STATUS_NON_MONOLITHIC),
            );

            return null;
        }

        $oldHash = $tokens->getCodeHash();
        $newHash = $oldHash;
        $new = $old;

        $appliedFixers = [];

        $ruleCustomisers = $this->ruleCustomisationPolicy->getRuleCustomisers(); // were already validated

        try {
            $fixerAnnotationAnalysis = (new FixerAnnotationAnalyzer())->find($tokens);
            $rulesIgnoredByAnnotations = $fixerAnnotationAnalysis['php-cs-fixer-ignore'] ?? [];
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(
                \sprintf(
                    'Error while analysing file "%s": %s',
                    $filePathname,
                    $e->getMessage(),
                ),
                $e->getCode(),
                $e,
            );
        }

        $this->validateRulesNamesForExceptions(
            $rulesIgnoredByAnnotations,
            <<<EOT
                @php-cs-fixer-ignore annotation(s) used for rules that are not in the current set of enabled rules:
                %s

                Please check your annotation(s) usage in {$filePathname} to ensure that these rules are included, or update your annotation(s) usage if they have been replaced by other rules in the version of PHP CS Fixer you are using.
                EOT,
        );

        try {
            foreach ($this->fixers as $fixer) {
                if (\in_array($fixer->getName(), $rulesIgnoredByAnnotations, true)) {
                    continue;
                }

                $customiser = $ruleCustomisers[$fixer->getName()] ?? null;
                if (null !== $customiser) {
                    $actualFixer = $customiser($file);
                    if (false === $actualFixer) {
                        continue;
                    }
                    if (true !== $actualFixer) {
                        if (\get_class($fixer) !== \get_class($actualFixer)) {
                            throw new \RuntimeException(\sprintf(
                                'The fixer returned by the Rule Customisation Policy must be of the same class as the original fixer (expected `%s`, got `%s`).',
                                \get_class($fixer),
                                \get_class($actualFixer),
                            ));
                        }
                        $fixer = $actualFixer;
                    }
                }

                // for custom fixers we don't know is it safe to run `->fix()` without checking `->supports()` and `->isCandidate()`,
                // thus we need to check it and conditionally skip fixing
                if (
                    !$fixer instanceof AbstractFixer
                    && (!$fixer->supports($file) || !$fixer->isCandidate($tokens))
                ) {
                    continue;
                }

                $fixer->fix($file, $tokens);

                if ($tokens->isChanged()) {
                    $tokens->clearEmptyTokens();
                    $tokens->clearChanged();
                    $appliedFixers[] = $fixer->getName();
                    if (filter_var(getenv('PHP_CS_FIXER_DEBUG'), \FILTER_VALIDATE_BOOL)) {
                        try {
                            $this->linter->lintSource($tokens->generateCode())->check();
                        } catch (LintingException $e) {
                            $this->dispatchEvent(FileProcessed::NAME, new FileProcessed(FileProcessed::STATUS_LINT));

                            $this->errorsManager->report(new Error(Error::TYPE_LINT, $filePathname, $e, [$fixer->getName()], $this->differ->diff($old, $tokens->generateCode(), $file)));

                            return null;
                        }
                    }
                }
            }
        } catch (\ParseError $e) {
            $this->dispatchEvent(FileProcessed::NAME, new FileProcessed(FileProcessed::STATUS_LINT));

            $this->errorsManager->report(new Error(Error::TYPE_LINT, $filePathname, $e));

            return null;
        } catch (\Throwable $e) {
            $this->processException($filePathname, $e);

            return null;
        }

        $fixInfo = null;

        if ([] !== $appliedFixers) {
            $new = $tokens->generateCode();
            $newHash = $tokens->getCodeHash();
        }

        // We need to check if content was changed and then applied changes.
        // But we can't simply check $appliedFixers, because one fixer may revert
        // work of other and both of them will mark collection as changed.
        // Therefore we need to check if code hashes changed.
        if ($oldHash !== $newHash) {
            $fixInfo = [
                'appliedFixers' => $appliedFixers,
                'diff' => $this->differ->diff($old, $new, $file),
            ];

            try {
                $this->linter->lintSource($new)->check();
            } catch (LintingException $e) {
                $this->dispatchEvent(FileProcessed::NAME, new FileProcessed(FileProcessed::STATUS_LINT));

                $this->errorsManager->report(new Error(Error::TYPE_LINT, $filePathname, $e, $fixInfo['appliedFixers'], $fixInfo['diff']));

                return null;
            }

            if (!$this->isDryRun) {
                $fileRealPath = $file->getRealPath();

                if (!file_exists($fileRealPath)) {
                    throw new IOException(
                        \sprintf('Failed to write file "%s" (no longer) exists.', $file->getPathname()),
                        0,
                        null,
                        $file->getPathname(),
                    );
                }

                if (is_dir($fileRealPath)) {
                    throw new IOException(
                        \sprintf('Cannot write file "%s" as the location exists as directory.', $fileRealPath),
                        0,
                        null,
                        $fileRealPath,
                    );
                }

                if (!is_writable($fileRealPath)) {
                    throw new IOException(
                        \sprintf('Cannot write to file "%s" as it is not writable.', $fileRealPath),
                        0,
                        null,
                        $fileRealPath,
                    );
                }

                if (false === @file_put_contents($fileRealPath, $new)) {
                    $error = error_get_last();

                    throw new IOException(
                        \sprintf('Failed to write file "%s", "%s".', $fileRealPath, null !== $error ? $error['message'] : 'no reason available'),
                        0,
                        null,
                        $fileRealPath,
                    );
                }
            }
        }

        $this->cacheManager->setFileHash($filePathname, $newHash);

        $this->dispatchEvent(
            FileProcessed::NAME,
            new FileProcessed(null !== $fixInfo ? FileProcessed::STATUS_FIXED : FileProcessed::STATUS_NO_CHANGES, $newHash),
        );

        return $fixInfo;
    }

    /**
     * Process an exception that occurred.
     */
    private function processException(string $name, \Throwable $e): void
    {
        $this->dispatchEvent(FileProcessed::NAME, new FileProcessed(FileProcessed::STATUS_EXCEPTION));

        $this->errorsManager->report(new Error(Error::TYPE_EXCEPTION, $name, $e));
    }

    private function dispatchEvent(string $name, Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event, $name);
    }

    private function getLintingFileIterator(): LintingResultAwareFileIteratorInterface
    {
        $fileFilterIterator = $this->getFilteringFileIterator();

        return $this->linter->isAsync()
            ? new FileCachingLintingFileIterator($fileFilterIterator, $this->linter)
            : new LintingFileIterator($fileFilterIterator, $this->linter);
    }

    private function getFilteringFileIterator(): FileFilterIterator
    {
        if (null === $this->fileIterator) {
            throw new \RuntimeException('File iterator is not configured. Pass paths during Runner initialisation or set them after with `setFileIterator()`.');
        }

        return new FileFilterIterator(
            $this->fileIterator instanceof \IteratorAggregate
                ? $this->fileIterator->getIterator()
                : $this->fileIterator,
            $this->eventDispatcher,
            $this->cacheManager,
        );
    }
}
