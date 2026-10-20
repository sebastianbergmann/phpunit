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

use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerOptionInterface;
use PhpCsFixer\Utils;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\HelpCommand as BaseHelpCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[AsCommand(name: 'help')]
final class HelpCommand extends BaseHelpCommand
{
    public function __construct()
    {
        parent::__construct('help');
    }

    /**
     * Formats the description of an option to include its allowed values.
     *
     * @param string                 $description   description with a single `%s` placeholder for the allowed values
     * @param non-empty-list<string> $allowedValues
     */
    public static function getDescriptionWithAllowedValues(string $description, array $allowedValues): string
    {
        $allowedValues = Utils::naturalLanguageJoinWithBackticks($allowedValues, 'or');

        return \sprintf($description, 'can be '.$allowedValues);
    }

    /**
     * Returns the allowed values of the given option that can be converted to a string.
     *
     * @return null|non-empty-list<AllowedValueSubset|mixed>
     */
    public static function getDisplayableAllowedValues(FixerOptionInterface $option): ?array
    {
        $allowed = $option->getAllowedValues();

        if (null !== $allowed) {
            $allowed = array_filter($allowed, static fn ($value): bool => !$value instanceof \Closure);

            usort($allowed, static function ($valueA, $valueB): int {
                if ($valueA instanceof AllowedValueSubset) {
                    return -1;
                }

                if ($valueB instanceof AllowedValueSubset) {
                    return 1;
                }

                return strcasecmp(
                    Utils::toString($valueA),
                    Utils::toString($valueB),
                );
            });

            if (0 === \count($allowed)) {
                $allowed = null;
            }
        }

        return $allowed;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $output->getFormatter()->setStyle('url', new OutputFormatterStyle('blue'));
    }
}
