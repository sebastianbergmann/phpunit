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

namespace PhpCsFixer\Linter;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TokenizerLintingResult implements LintingResultInterface
{
    private ?\Error $error;

    public function __construct(?\Error $error = null)
    {
        $this->error = $error;
    }

    public function check(): void
    {
        if (null !== $this->error) {
            throw new LintingException(
                \sprintf('%s: %s on line %d.', $this->getMessagePrefix(), $this->error->getMessage(), $this->error->getLine()),
                $this->error->getCode(),
                $this->error,
            );
        }
    }

    private function getMessagePrefix(): string
    {
        return $this->error instanceof \ParseError ? 'Parse error' : 'Fatal error';
    }
}
