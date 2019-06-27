<?php declare(strict_types=1);
namespace Issue3739;

use PHPUnit\Framework\TestCase;

class Issue3739
{
    public function unlinkFileThatDoesNotExistWithErrorSuppression(): bool
    {
        return @unlink(__DIR__ . '/DOES_NOT_EXIST');
    }

    public function unlinkFileThatDoesNotExistWithoutErrorSuppression(): bool
    {
        return unlink(__DIR__ . '/DOES_NOT_EXIST');
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
