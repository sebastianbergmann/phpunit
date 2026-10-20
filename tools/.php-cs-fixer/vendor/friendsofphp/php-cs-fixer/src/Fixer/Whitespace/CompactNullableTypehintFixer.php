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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
 * @author Jack Cherng <jfcherng@gmail.com>
 *
 * @deprecated
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CompactNullableTypehintFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
    private CompactNullableTypeDeclarationFixer $compactNullableTypeDeclarationFixer;

    public function __construct()
    {
        $this->compactNullableTypeDeclarationFixer = new CompactNullableTypeDeclarationFixer();

        parent::__construct();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        $fixerDefinition = $this->compactNullableTypeDeclarationFixer->getDefinition();

        return new FixerDefinition(
            'Remove extra spaces in a nullable typehint.',
            $fixerDefinition->getCodeSamples(),
            $fixerDefinition->getDescription(),
            $fixerDefinition->getRiskyDescription(),
        );
    }

    public function getSuccessorsNames(): array
    {
        return [
            $this->compactNullableTypeDeclarationFixer->getName(),
        ];
    }

    protected function createProxyFixers(): array
    {
        return [
            $this->compactNullableTypeDeclarationFixer,
        ];
    }
}
