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

namespace PhpCsFixer\Differ;

use PhpCsFixer\Preg;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DiffConsoleFormatter
{
    private bool $isDecoratedOutput;

    private string $template;

    public function __construct(bool $isDecoratedOutput, string $template = '%s')
    {
        $this->isDecoratedOutput = $isDecoratedOutput;
        $this->template = $template;
    }

    public function format(string $diff, string $lineTemplate = '%s'): string
    {
        $isDecorated = $this->isDecoratedOutput;

        $template = $isDecorated
            ? $this->template
            : Preg::replace('/<[^<>]+>/', '', $this->template);

        return \sprintf(
            $template,
            implode(
                \PHP_EOL,
                array_map(
                    static function (string $line) use ($isDecorated, $lineTemplate): string {
                        if ($isDecorated) {
                            $count = 0;
                            $line = Preg::replaceCallback(
                                '/^([+\-@].*)/',
                                static function (array $matches): string {
                                    if ('+' === $matches[0][0]) {
                                        $colour = 'green';
                                    } elseif ('-' === $matches[0][0]) {
                                        $colour = 'red';
                                    } else {
                                        $colour = 'cyan';
                                    }

                                    return \sprintf('<fg=%s>%s</fg=%s>', $colour, OutputFormatter::escape($matches[0]), $colour);
                                },
                                $line,
                                1,
                                $count,
                            );

                            if (0 === $count) {
                                $line = OutputFormatter::escape($line);
                            }
                        }

                        return \sprintf($lineTemplate, $line);
                    },
                    Preg::split('#\R#u', $diff),
                ),
            ),
        );
    }
}
