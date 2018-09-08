<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

interface OriginalType
{
    public function getCodePrologue(): string;

    public function getCodeEpilogue(): string;

    public function hasMethod(string $name): bool;

    /**
     * @throws \OutOfBoundsException if method does not exist
     */
    public function getMethod(string $name): \ReflectionMethod;

    public function isInterface(): bool;

    public function getName(): TypeName;

    public function getMethods(): array;

    public function isFinal(): bool;

    public function implementsInterface(string $interface): bool;
}
