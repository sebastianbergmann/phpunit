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

namespace PhpCsFixer\Console\Output;

use PhpCsFixer\Differ\DiffConsoleFormatter;
use PhpCsFixer\Error\Error;
use PhpCsFixer\Linter\LintingException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ErrorOutput
{
    private OutputInterface $output;

    private bool $isDecorated;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->isDecorated = $output->isDecorated();
    }

    /**
     * @param list<Error> $errors
     */
    public function listErrors(string $process, array $errors): void
    {
        $this->output->writeln(['', \sprintf(
            'Files that were not fixed due to errors reported during %s:',
            $process,
        )]);

        $showDetails = $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
        $showTrace = $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
        foreach ($errors as $i => $error) {
            $this->output->writeln(\sprintf('%4d) %s', $i + 1, $error->getFilePath()));
            $e = $error->getSource();
            if (!$showDetails || null === $e) {
                continue;
            }

            $class = \sprintf('[%s]', \get_class($e));
            $message = $e->getMessage();
            $code = $e->getCode();
            if (0 !== $code) {
                $message .= " ({$code})";
            }

            $length = max(\strlen($class), \strlen($message));
            $lines = [
                '',
                $class,
                $message,
                '',
            ];

            $this->output->writeln('');

            foreach ($lines as $line) {
                if (\strlen($line) < $length) {
                    $line .= str_repeat(' ', $length - \strlen($line));
                }

                $this->output->writeln(\sprintf('      <error>  %s  </error>', $this->prepareOutput($line)));
            }

            if ($showTrace && !$e instanceof LintingException) { // stack trace of lint exception is of no interest
                $this->output->writeln('');
                $stackTrace = $e->getTrace();
                foreach ($stackTrace as $trace) {
                    if (isset($trace['class']) && Command::class === $trace['class'] && 'run' === $trace['function']) {
                        $this->output->writeln('      [ ... ]');

                        break;
                    }

                    $this->outputTrace($trace);
                }
            }

            if (Error::TYPE_LINT === $error->getType() && 0 < \count($error->getAppliedFixers())) {
                $this->output->writeln('');
                $this->output->writeln(\sprintf('      Applied fixers: <comment>%s</comment>', implode(', ', $error->getAppliedFixers())));

                $diff = $error->getDiff();
                if ('' !== $diff) {
                    $diffFormatter = new DiffConsoleFormatter(
                        $this->isDecorated,
                        \sprintf(
                            '<comment>      ---------- begin diff ----------</comment>%s%%s%s<comment>      ----------- end diff -----------</comment>',
                            \PHP_EOL,
                            \PHP_EOL,
                        ),
                    );

                    $this->output->writeln($diffFormatter->format($diff));
                }
            }
        }
    }

    /**
     * @param array{
     *     function?: string,
     *     line?: int,
     *     file?: string,
     *     class?: class-string,
     *     type?: '->'|'::',
     *     args?: list<mixed>,
     *     object?: object,
     * } $trace
     */
    private function outputTrace(array $trace): void
    {
        if (isset($trace['class'], $trace['type'], $trace['function'])) {
            $this->output->writeln(\sprintf(
                '      <comment>%s</comment>%s<comment>%s()</comment>',
                $this->prepareOutput($trace['class']),
                $this->prepareOutput($trace['type']),
                $this->prepareOutput($trace['function']),
            ));
        } elseif (isset($trace['function'])) {
            $this->output->writeln(\sprintf('      <comment>%s()</comment>', $this->prepareOutput($trace['function'])));
        }

        if (isset($trace['file'])) {
            $this->output->writeln(
                \sprintf('        in <info>%s</info>', $this->prepareOutput($trace['file']))
                .(isset($trace['line']) ? \sprintf(' at line <info>%d</info>', $trace['line']) : ' at unknown line'),
            );
        }
    }

    private function prepareOutput(string $string): string
    {
        return $this->isDecorated
            ? OutputFormatter::escape($string)
            : $string;
    }
}
