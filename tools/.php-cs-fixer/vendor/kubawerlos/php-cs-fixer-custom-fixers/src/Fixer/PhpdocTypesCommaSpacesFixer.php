<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;

/**
 * @no-named-arguments
 */
final class PhpdocTypesCommaSpacesFixer extends AbstractTypesFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPDoc types commas must not be preceded by a whitespace, and must be succeeded by a single whitespace.',
            [new CodeSample("<?php /** @var array<int,string> */\n")],
            '',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    protected function fixType(string $type): string
    {
        return Preg::replace('/\\h*,\\s*/', ', ', $type);
    }
}
