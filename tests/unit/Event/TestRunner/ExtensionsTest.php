<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\TestRunner\Extensions
 */
final class ExtensionsTest extends TestCase
{
    public function testLoadedReturnsFalseWhenExtensionIsNotLoaded(): void
    {
        $extensions = new Extensions();

        $this->assertFalse($extensions->loaded('foo'));
    }

    public function testLoadedReturnsTrueWhenExtensionIsLoaded(): void
    {
        $extensions = new Extensions();

        $this->assertTrue($extensions->loaded('json'));
    }

    public function getIteratorReturnsIteratorWithAllExtensions(): void
    {
        $expected = array_merge(
            get_loaded_extensions(true),
            get_loaded_extensions(false)
        );

        asort($expected);

        $extensions = new Extensions();

        $this->assertSame($expected, iterator_to_array($extensions->getIterator()));
    }
}
