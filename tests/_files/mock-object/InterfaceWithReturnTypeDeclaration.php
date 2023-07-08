<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\MockObject;

interface InterfaceWithReturnTypeDeclaration
{
    public function __toString(): string;

    public function doSomething(): bool;

    public function doSomethingElse(int $x): int;

    public function selfReference(): self;

    public function returnsNullOrString(): ?string;
}
