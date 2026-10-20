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

namespace PhpCsFixer\Console\Report\ListRulesReport;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ReporterFactory
{
    /**
     * @var array<string, ReporterInterface>
     */
    private array $reporters = [];

    public function getReporter(string $format): ReporterInterface
    {
        if (!isset($this->reporters[$format])) {
            throw new \UnexpectedValueException(\sprintf('The format "%s" is not defined.', $format));
        }

        return $this->reporters[$format];
    }

    /**
     * @return list<string>
     */
    public function getFormats(): array
    {
        $formats = array_keys($this->reporters);
        sort($formats);

        return $formats;
    }

    /**
     * @return $this
     */
    public function registerBuiltInReporters(): self
    {
        $this
            ->registerReporter(new JsonReporter())
            ->registerReporter(new TextReporter())
        ;

        return $this;
    }

    /**
     * @return $this
     */
    public function registerReporter(ReporterInterface $reporter): self
    {
        $format = $reporter->getFormat();

        if (isset($this->reporters[$format])) {
            throw new \UnexpectedValueException(\sprintf('Reporter for format "%s" is already registered.', $format));
        }

        $this->reporters[$format] = $reporter;

        return $this;
    }
}
