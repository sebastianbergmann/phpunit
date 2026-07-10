<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\UsesDirectory;
use PHPUnit\Framework\Attributes\UsesDirectoryRecursively;
use PHPUnit\Framework\Attributes\UsesFile;
use PHPUnit\Framework\TestCase;

#[UsesFile('source.php')]
#[UsesDirectory('source')]
#[UsesDirectoryRecursively('source')]
final class UsesFilesystemTest extends TestCase
{
    public function testOne(): void
    {
    }
}
