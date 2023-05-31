<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use function count;
use function explode;
use function extension_loaded;
use function implode;
use function is_file;
use function str_contains;
use PharIo\Manifest\ApplicationName;
use PharIo\Manifest\Exception as ManifestException;
use PharIo\Manifest\ManifestLoader;
use PharIo\Version\Version as PharIoVersion;
use PHPUnit\Event;
use PHPUnit\Runner\Version;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PharLoader
{
    /**
     * @psalm-return array{loadedExtensions: list<string>, notLoadedExtensions: list<string>}
     */
    public function loadPharExtensionsInDirectory(string $directory): array
    {
        $pharExtensionLoaded = extension_loaded('phar');

        if (!$pharExtensionLoaded) {
            Event\Facade::emitter()->testRunnerTriggeredWarning(
                'Loading PHPUnit extension(s) from PHP archive(s) failed, PHAR extension not loaded',
            );
        }

        $loadedExtensions    = [];
        $notLoadedExtensions = [];

        foreach ((new FileIteratorFacade)->getFilesAsArray($directory, '.phar') as $file) {
            if (!$pharExtensionLoaded) {
                $notLoadedExtensions[] = $file . ' cannot be loaded';

                continue;
            }

            if (!is_file('phar://' . $file . '/manifest.xml')) {
                $notLoadedExtensions[] = $file . ' is not an extension for PHPUnit';

                continue;
            }

            try {
                $applicationName = new ApplicationName('phpunit/phpunit');
                $version         = new PharIoVersion($this->phpunitVersion());
                $manifest        = ManifestLoader::fromFile('phar://' . $file . '/manifest.xml');

                if (!$manifest->isExtensionFor($applicationName)) {
                    $notLoadedExtensions[] = $file . ' is not an extension for PHPUnit';

                    continue;
                }

                if (!$manifest->isExtensionFor($applicationName, $version)) {
                    $notLoadedExtensions[] = $file . ' is not compatible with this version of PHPUnit';

                    continue;
                }
            } catch (ManifestException $e) {
                $notLoadedExtensions[] = $file . ': ' . $e->getMessage();

                continue;
            }

            try {
                /** @psalm-suppress UnresolvableInclude */
                @require $file;
            } catch (Throwable $t) {
                $notLoadedExtensions[] = $file . ': ' . $t->getMessage();

                continue;
            }

            $loadedExtensions[] = $manifest->getName()->asString() . ' ' . $manifest->getVersion()->getVersionString();

            Event\Facade::emitter()->testRunnerLoadedExtensionFromPhar(
                $file,
                $manifest->getName()->asString(),
                $manifest->getVersion()->getVersionString(),
            );
        }

        return [
            'loadedExtensions'    => $loadedExtensions,
            'notLoadedExtensions' => $notLoadedExtensions,
        ];
    }

    private function phpunitVersion(): string
    {
        $version = Version::id();

        if (!str_contains($version, '-')) {
            return $version;
        }

        $parts = explode('.', explode('-', $version)[0]);

        if (count($parts) === 2) {
            $parts[] = 0;
        }

        return implode('.', $parts);
    }
}
