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

use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Console\Report\ListRulesReport\ReporterFactory;
use PhpCsFixer\Console\Report\ListRulesReport\ReporterInterface;
use PhpCsFixer\Console\Report\ListRulesReport\ReportSummary;
use PhpCsFixer\Console\Report\ListRulesReport\TextReporter;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\Utils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'list-rules', description: 'List all available Rules.')]
final class ListRulesCommand extends Command
{
    public function __construct()
    {
        parent::__construct('list-rules');
        $this->setDescription('List all available Rules.');
    }

    protected function configure(): void
    {
        $reporterFactory = new ReporterFactory();
        $reporterFactory->registerBuiltInReporters();
        $formats = $reporterFactory->getFormats();
        \assert([] !== $formats);

        $this->setDefinition(
            [
                new InputOption('format', '', InputOption::VALUE_REQUIRED, HelpCommand::getDescriptionWithAllowedValues('To output results in other formats (%s).', $formats), (new TextReporter())->getFormat(), $formats),
            ],
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reporter = $this->resolveReporterWithFactory(
            $input->getOption('format'),
            new ReporterFactory(),
        );

        $fixerFactory = new FixerFactory();
        $fixerFactory->registerBuiltInFixers();

        $reportSummary = new ReportSummary(
            $fixerFactory->getFixers(),
        );

        $report = $reporter->generate($reportSummary);

        $output->isDecorated()
            ? $output->write(OutputFormatter::escape($report))
            : $output->write($report, false, OutputInterface::OUTPUT_RAW);

        return 0;
    }

    private function resolveReporterWithFactory(string $format, ReporterFactory $factory): ReporterInterface
    {
        try {
            $factory->registerBuiltInReporters();
            $reporter = $factory->getReporter($format);
        } catch (\UnexpectedValueException $e) {
            $formats = $factory->getFormats();
            sort($formats);

            throw new InvalidConfigurationException(\sprintf('The format "%s" is not defined, supported are %s.', $format, Utils::naturalLanguageJoin($formats)));
        }

        return $reporter;
    }
}
