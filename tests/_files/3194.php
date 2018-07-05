<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

trait T3194
{
    public function doSomethingElse(): bool
    {
        return true;
    }
}

final class C3194
{
    use T3194;

    public function doSomething(): bool
    {
        return $this->doSomethingElse();
    }
}

/**
 * @covers C3194
 */
final class Test3194 extends TestCase
{
    public function testOne(): void
    {
        $o = new C;

        $this->assertTrue($o->doSomething());
    }
}
