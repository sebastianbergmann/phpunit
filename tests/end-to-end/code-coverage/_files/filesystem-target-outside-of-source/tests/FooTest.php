<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\FilesystemTargetOutsideOfSource;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversDirectory;
use PHPUnit\Framework\Attributes\CoversDirectoryRecursively;
use PHPUnit\Framework\Attributes\CoversFile;
use PHPUnit\Framework\Attributes\UsesDirectory;
use PHPUnit\Framework\TestCase;

#[CoversClass(Foo::class)]
#[CoversFile(__DIR__ . '/../src/Foo.php')]
#[CoversFile(__DIR__ . '/../src/DoesNotExist.php')]
#[CoversDirectory(__DIR__ . '/../src')]
#[CoversDirectory(__DIR__ . '/../src/Sub')]
#[CoversFile(__FILE__)]
#[CoversDirectory(__DIR__)]
#[CoversDirectoryRecursively(__DIR__)]
#[UsesDirectory(__DIR__ . '/..')]
final class FooTest extends TestCase
{
    public function testDoSomething(): void
    {
        $this->assertTrue((new Foo)->doSomething());
        $this->assertTrue((new Bar)->doSomethingElse());
    }
}
