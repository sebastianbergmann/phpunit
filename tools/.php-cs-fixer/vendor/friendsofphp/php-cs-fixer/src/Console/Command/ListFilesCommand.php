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
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\ToolInfoInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

/**
 * @author Markus Staab <markus.staab@redaxo.org>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'list-files', description: 'List all files being fixed by the given config.')]
final class ListFilesCommand extends Command
{
    private ConfigInterface $defaultConfig;

    private ToolInfoInterface $toolInfo;

    public function __construct(ToolInfoInterface $toolInfo)
    {
        parent::__construct('list-files');
        $this->setDescription('List all files being fixed by the given config.');

        $this->defaultConfig = new Config();
        $this->toolInfo = $toolInfo;
    }

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The path to a .php-cs-fixer.php file.'),
            ],
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $passedConfig = $input->getOption('config');

        $cwd = getcwd();
        \assert(false !== $cwd);

        $resolver = new ConfigurationResolver(
            $this->defaultConfig,
            [
                'config' => $passedConfig,
            ],
            $cwd,
            $this->toolInfo,
        );

        $finder = $resolver->getFinder();

        foreach ($finder as $file) {
            if ($file->isFile()) {
                $relativePath = './'.Path::makeRelative($file->getRealPath(), $cwd);
                // unify directory separators across operating system
                $relativePath = str_replace('/', \DIRECTORY_SEPARATOR, $relativePath);

                $output->writeln(escapeshellarg($relativePath));
            }
        }

        return 0;
    }
}
