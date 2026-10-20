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

namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
 * @deprecated in favour of NativeTypeDeclarationCasingFixer
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NativeFunctionTypeDeclarationCasingFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Native type declarations for functions should use the correct case.',
            [
                new CodeSample("<?php\nclass Bar {\n    public function Foo(CALLABLE \$bar)\n    {\n        return 1;\n    }\n}\n"),
                new CodeSample(
                    "<?php\nfunction Foo(INT \$a): Bool\n{\n    return true;\n}\n",
                ),
                new CodeSample(
                    "<?php\nfunction Foo(Iterable \$a): VOID\n{\n    echo 'Hello world';\n}\n",
                ),
                new CodeSample(
                    "<?php\nfunction Foo(Object \$a)\n{\n    return 'hi!';\n}\n",
                ),
            ],
        );
    }

    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    protected function createProxyFixers(): array
    {
        $fixer = new NativeTypeDeclarationCasingFixer();

        return [$fixer];
    }
}
