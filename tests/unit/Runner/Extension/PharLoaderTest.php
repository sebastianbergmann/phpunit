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

use const DIRECTORY_SEPARATOR;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(PharLoader::class)]
#[Medium]
final class PharLoaderTest extends TestCase
{
    #[TestDox('Does not load extension from file that is not a PHAR')]
    public function testDoesNotLoadExtensionFromFileThatIsNotAPhar(): void
    {
        $this->assertSame([], $this->loadExtensionsFrom('not-a-phar'));
    }

    #[TestDox('Does not load extension from PHAR that is not an extension for PHPUnit')]
    public function testDoesNotLoadExtensionFromPharThatIsNotAnExtensionForPhpunit(): void
    {
        $this->assertSame([], $this->loadExtensionsFrom('not-an-extension'));
    }

    #[TestDox('Does not load extension from PHAR that is not compatible with this version of PHPUnit')]
    public function testDoesNotLoadExtensionFromPharThatIsNotCompatibleWithThisVersionOfPhpunit(): void
    {
        $this->assertSame([], $this->loadExtensionsFrom('incompatible-extension'));
    }

    #[TestDox('Does not load extension from PHAR with a manifest that cannot be parsed')]
    public function testDoesNotLoadExtensionFromPharWithManifestThatCannotBeParsed(): void
    {
        $this->assertSame([], $this->loadExtensionsFrom('invalid-manifest'));
    }

    #[TestDox('Does not load extension from PHAR that cannot be required')]
    public function testDoesNotLoadExtensionFromPharThatCannotBeRequired(): void
    {
        $this->assertSame([], $this->loadExtensionsFrom('extension-that-cannot-be-loaded'));
    }

    /**
     * @param non-empty-string $directory
     *
     * @return list<string>
     */
    private function loadExtensionsFrom(string $directory): array
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .
                     DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'phar-loader' . DIRECTORY_SEPARATOR . $directory;

        /*
         * PharLoader emits test runner warnings for extensions that cannot be
         * loaded. These must not end up in the result of the test run that
         * exercises PharLoader, so they are emitted into a throw-away event
         * facade that is never forwarded.
         */
        $property = new ReflectionProperty(Facade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new Facade);

        try {
            return (new PharLoader)->loadPharExtensionsInDirectory($directory);
        } finally {
            $property->setValue(null, $facade);
        }
    }
}
