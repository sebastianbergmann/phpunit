<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrossFile;

use PHPUnit\Framework\TestCase;

final class BUsingTest extends TestCase implements SharedInterface
{
    use SharedTrait;

    public function sharedInterfaceMethod(): string
    {
        return 'shared interface';
    }

    public function testUsesTheInterfaceTheTraitTheFunctionAndTheConstantDeclaredInAnotherTestClassFile(): void
    {
        $this->assertSame('shared interface', $this->sharedInterfaceMethod());
        $this->assertSame('shared trait', $this->sharedTraitMethod());
        $this->assertSame('shared function', sharedFunction());
        $this->assertSame('shared constant', SHARED_CONSTANT);
    }
}
