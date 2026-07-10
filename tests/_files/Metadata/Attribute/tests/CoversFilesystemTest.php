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

use PHPUnit\Framework\Attributes\CoversDirectory;
use PHPUnit\Framework\Attributes\CoversDirectoryRecursively;
use PHPUnit\Framework\Attributes\CoversFile;
use PHPUnit\Framework\TestCase;

#[CoversFile('source.php')]
#[CoversDirectory('source')]
#[CoversDirectoryRecursively('source')]
final class CoversFilesystemTest extends TestCase
{
    public function testOne(): void
    {
    }
}
