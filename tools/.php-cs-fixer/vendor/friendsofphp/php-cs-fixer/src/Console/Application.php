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

use PhpCsFixer\Console\Command\CheckCommand;
use PhpCsFixer\Console\Command\DescribeCommand;
use PhpCsFixer\Console\Command\FixCommand;
use PhpCsFixer\Console\Command\HelpCommand;
use PhpCsFixer\Console\Command\InitCommand;
use PhpCsFixer\Console\Command\ListFilesCommand;
use PhpCsFixer\Console\Command\ListRulesCommand;
use PhpCsFixer\Console\Command\ListSetsCommand;
use PhpCsFixer\Console\Command\SelfUpdateCommand;
use PhpCsFixer\Console\Command\WorkerCommand;
use PhpCsFixer\Console\SelfUpdate\GithubClient;
use PhpCsFixer\Console\SelfUpdate\NewVersionChecker;
use PhpCsFixer\Future;
use PhpCsFixer\PharChecker;
use PhpCsFixer\Runner\Parallel\WorkerException;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\CompleteCommand;
use Symfony\Component\Console\Command\DumpCompletionCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Application extends BaseApplication
{
    public const NAME = 'PHP CS Fixer';
    public const VERSION = '3.95.27';
    public const VERSION_CODENAME = 'Adalbertus';

    /**
     * @readonly
     */
    private ToolInfo $toolInfo;

    private ?Command $executedCommand = null;

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->toolInfo = new ToolInfo();

        // in alphabetical order
        $this->add(new CheckCommand($this->toolInfo));
        $this->add(new DescribeCommand());
        $this->add(new FixCommand($this->toolInfo));
        $this->add(new InitCommand());
        $this->add(new ListFilesCommand($this->toolInfo));
        $this->add(new ListRulesCommand());
        $this->add(new ListSetsCommand());
        $this->add(new SelfUpdateCommand(
            new NewVersionChecker(new GithubClient()),
            $this->toolInfo,
            new PharChecker(),
        ));
        $this->add(new WorkerCommand($this->toolInfo));
    }

    // polyfill for `add` method, as it is not available in Symfony 8.0
    public function add(Command $command): ?Command
    {
        if (method_exists($this, 'addCommand')) { // @phpstan-ignore-line
            return $this->addCommand($command);
        }

        return parent::add($command); // @phpstan-ignore-line
    }

    public static function getMajorVersion(): int
    {
        return (int) explode('.', self::VERSION)[0];
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $stdErr = $output instanceof ConsoleOutputInterface
            ? $output->getErrorOutput()
            : ($input->hasParameterOption('--format', true) && 'txt' !== $input->getParameterOption('--format', null, true) ? null : $output);

        if (null !== $stdErr) {
            $warningsDetector = new WarningsDetector($this->toolInfo);
            $warningsDetector->detectOldVendor();
            $warningsDetector->detectOldMajor();

            try {
                $commandName = $this->getCommandName($input);
                if (null === $commandName) {
                    throw new CommandNotFoundException('No command name found.');
                }
                $command = $this->find($commandName);

                if (($command instanceof CheckCommand) || ($command instanceof FixCommand)) {
                    $warningsDetector->detectHigherPhpVersion();
                    $warningsDetector->detectNonMonolithic();
                }
            } catch (CommandNotFoundException $e) {
                // no-op
            }

            $warnings = $warningsDetector->getWarnings();

            if (\count($warnings) > 0) {
                foreach ($warnings as $warning) {
                    $stdErr->writeln(\sprintf($stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s', $warning));
                }
                $stdErr->writeln('');
            }
        }

        $result = parent::doRun($input, $output);

        if (
            null !== $stdErr
            && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE
        ) {
            $triggeredDeprecations = Future::getTriggeredDeprecations();

            if (\count($triggeredDeprecations) > 0) {
                $stdErr->writeln('');
                $stdErr->writeln($stdErr->isDecorated() ? '<bg=yellow;fg=black;>Detected deprecations in use (they will stop working in next major release):</>' : 'Detected deprecations in use (they will stop working in next major release):');
                foreach ($triggeredDeprecations as $deprecation) {
                    $stdErr->writeln(\sprintf('- %s', $deprecation));
                }
            }
        }

        return $result;
    }

    /**
     * @internal
     */
    public static function getAbout(bool $decorated = false): string
    {
        $longVersion = \sprintf('%s <info>%s</info>', self::NAME, self::VERSION);

        // value of `$commitPlaceholderPossiblyEvaluated` will be changed during phar building, other value will not
        $commitPlaceholderPossiblyEvaluated = '@git-commit@';
        $commitPlaceholder = implode('', ['@', 'git-commit@']); // do not replace with imploded value, as here we need to prevent phar builder to replace the placeholder

        $versionCommit = $commitPlaceholder !== $commitPlaceholderPossiblyEvaluated
            ? substr($commitPlaceholderPossiblyEvaluated, 0, 7) // for phar builds
            : '';

        $about = implode('', [
            $longVersion,
            $versionCommit ? \sprintf(' <info>(%s)</info>', $versionCommit) : '', // @phpstan-ignore-line to avoid `Ternary operator condition is always true|false.`
            self::VERSION_CODENAME ? \sprintf(' <info>%s</info>', self::VERSION_CODENAME) : '', // @phpstan-ignore-line to avoid `Ternary operator condition is always true|false.`
            ' by <comment>Fabien Potencier</comment>, <comment>Dariusz Ruminski</comment> and <comment>contributors</comment>.',
        ]);

        if (false === $decorated) {
            return strip_tags($about);
        }

        return $about;
    }

    /**
     * @internal
     */
    public static function getAboutWithRuntime(bool $decorated = false): string
    {
        $about = self::getAbout(true)."\nPHP runtime: <info>".\PHP_VERSION.'</info>';
        if (false === $decorated) {
            return strip_tags($about);
        }

        return $about;
    }

    public function getLongVersion(): string
    {
        return self::getAboutWithRuntime(true);
    }

    protected function getDefaultCommands(): array
    {
        return [new HelpCommand(), new ListCommand(), new CompleteCommand(), new DumpCompletionCommand()];
    }

    /**
     * @throws \Throwable
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        $this->executedCommand = $command;

        return parent::doRunCommand($command, $input, $output);
    }

    protected function doRenderThrowable(\Throwable $e, OutputInterface $output): void
    {
        // Since parallel analysis utilises child processes, and they have their own output,
        // we need to capture the output of the child process to determine it there was an exception.
        // Default render format is not machine-friendly, so we need to override it for `worker` command,
        // in order to be able to easily parse exception data for further displaying on main process' side.
        if ($this->executedCommand instanceof WorkerCommand) {
            $output->writeln(WorkerCommand::ERROR_PREFIX.json_encode(
                [
                    'class' => \get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTraceAsString(),
                ],
                \JSON_THROW_ON_ERROR,
            ));

            return;
        }

        parent::doRenderThrowable($e, $output);

        if ($output->isVeryVerbose() && $e instanceof WorkerException) {
            $output->writeln('<comment>Original trace from worker:</comment>');
            $output->writeln('');
            $output->writeln($e->getOriginalTraceAsString());
            $output->writeln('');
        }
    }
}
