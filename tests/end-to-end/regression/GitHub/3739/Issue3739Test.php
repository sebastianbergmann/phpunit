<?php declare(strict_types=1);
namespace Issue3739;

use PHPUnit\Framework\TestCase;

class Issue3739
{
    public function unlinkFileThatDoesNotExist(): bool
    {
        return @unlink(__DIR__ . '/DOES_NOT_EXIST');
    }
}

final class Issue3739Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertFalse((new Issue3739())->unlinkFileThatDoesNotExist());
    }
}
