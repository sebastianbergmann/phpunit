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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionBootstrap::class)]
#[Small]
final class ExtensionBootstrapTest extends TestCase
{
    public function testHasClassName(): void
    {
        $className = 'ClassName';

        $this->assertSame($className, (new ExtensionBootstrap($className, []))->className());
    }

    public function testHasParameters(): void
    {
        $parameters = ['foo' => 'bar'];

        $this->assertSame($parameters, (new ExtensionBootstrap('ClassName', $parameters))->parameters());
    }
}
