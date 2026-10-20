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

use PhpCsFixer\Preg;
use PhpCsFixer\ToolInfoInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'check', description: 'Checks if configured files/directories comply with configured rules.')]
final class CheckCommand extends FixCommand
{
    public function __construct(ToolInfoInterface $toolInfo)
    {
        parent::__construct($toolInfo);
        $this->setName('check');
        $this->setDescription('Checks if configured files/directories comply with configured rules.');
    }

    public function getHelp(): string
    {
        return Preg::replace('@\v\V*<comment>--dry-run</comment>\V*\v@', '', parent::getHelp());
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDefinition([
            ...array_values($this->getDefinition()->getArguments()),
            ...array_values(array_filter(
                $this->getDefinition()->getOptions(),
                static fn (InputOption $option): bool => 'dry-run' !== $option->getName(),
            )),
        ]);
    }

    protected function isDryRun(InputInterface $input): bool
    {
        return true;
    }
}
