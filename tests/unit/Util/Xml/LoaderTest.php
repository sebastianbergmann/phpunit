<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use PHPUnit\Framework\TestCase;

/**
 * @small
 *
 * @covers \PHPUnit\Util\Xml\Loader
 */
final class LoaderTest extends TestCase
{
    public function testCannotLoadXmlFromEmptyString(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not load XML from empty string');

        (new Loader)->load('');
    }
}
