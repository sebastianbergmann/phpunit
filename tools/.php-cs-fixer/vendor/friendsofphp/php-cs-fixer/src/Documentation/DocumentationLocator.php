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

namespace PhpCsFixer\Documentation;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Utils;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DocumentationLocator
{
    private string $path;

    public function __construct()
    {
        $this->path = \dirname(__DIR__, 2).'/doc';
    }

    public function getFixersDocumentationDirectoryPath(): string
    {
        return $this->path.'/rules';
    }

    public function getFixersDocumentationIndexFilePath(): string
    {
        return $this->getFixersDocumentationDirectoryPath().'/index.rst';
    }

    public function getFixerDocumentationFilePath(FixerInterface $fixer): string
    {
        return $this->getFixersDocumentationDirectoryPath().'/'.Preg::replaceCallback(
            '/^.*\\\(.+)\\\(.+)Fixer$/',
            static fn (array $matches): string => Utils::camelCaseToUnderscore($matches[1]).'/'.Utils::camelCaseToUnderscore($matches[2]),
            \get_class($fixer),
        ).'.rst';
    }

    public function getFixerDocumentationFileRelativePath(FixerInterface $fixer): string
    {
        return Preg::replace(
            '#^'.preg_quote($this->getFixersDocumentationDirectoryPath(), '#').'/#',
            '',
            $this->getFixerDocumentationFilePath($fixer),
        );
    }

    public function getRuleSetsDocumentationDirectoryPath(): string
    {
        return $this->path.'/ruleSets';
    }

    public function getRuleSetsDocumentationIndexFilePath(): string
    {
        return $this->getRuleSetsDocumentationDirectoryPath().'/index.rst';
    }

    public function getRuleSetsDocumentationFilePath(string $name): string
    {
        return $this->getRuleSetsDocumentationDirectoryPath().'/'.str_replace(':risky', 'Risky', ucfirst(substr($name, 1))).'.rst';
    }

    public function getUsageFilePath(): string
    {
        return $this->path.'/usage.rst';
    }
}
