<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const DIRECTORY_SEPARATOR;
use function ltrim;
use function realpath;
use function str_replace;
use PHPUnit\Framework\TestCase;

abstract class AbstractSouceFilterTestCase extends TestCase
{
    protected static function createSource(
        ?FilterDirectoryCollection $includeDirectories = null,
        ?FilterDirectoryCollection $excludeDirectories = null,
        ?FileCollection $includeFiles = null,
        ?FileCollection $excludeFiles = null,
    ): Source {
        return new Source(
            null,
            false,
            $includeDirectories ?? FilterDirectoryCollection::fromArray([]),
            $includeFiles ?? FileCollection::fromArray([]),
            $excludeDirectories ?? FilterDirectoryCollection::fromArray([]),
            $excludeFiles ?? FileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
        );
    }

    protected static function fixturePath(?string $subPath = null): string
    {
        $path = realpath(__DIR__ . '/../..') . '/_files/source-filter';

        if ($subPath !== null) {
            $path = $path . '/' . ltrim($subPath, '/');
        }

        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
