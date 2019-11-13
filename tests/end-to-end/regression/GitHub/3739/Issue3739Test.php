<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Issue3739;

use PHPUnit\Framework\TestCase;

class Issue3739
{
    public function unlinkFileThatDoesNotExistWithErrorSuppression(): bool
    {
        $path = $this->getPath();

        return @\unlink($path);
    }

    public function unlinkFileThatDoesNotExistWithoutErrorSuppression(): bool
    {
        $path = $this->getPath();

        return \unlink($path);
    }

    private function getPath()
    {
        $path = __DIR__ . '/DOES_NOT_EXIST';

        if (\DIRECTORY_SEPARATOR === '\\') {
            $path = \strtr($path, '\\', '/');
        }

        return $path;
    }
}

final class Issue3739Test extends TestCase
{
    public function testWithErrorSuppression(): void
    {
        $this->assertFalse((new Issue3739())->unlinkFileThatDoesNotExistWithErrorSuppression());
    }

    public function testWithoutErrorSuppression(): void
    {
        $this->assertFalse((new Issue3739())->unlinkFileThatDoesNotExistWithoutErrorSuppression());
    }
}
