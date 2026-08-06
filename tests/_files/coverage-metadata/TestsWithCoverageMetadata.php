<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CoverageMetadata;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatExtendClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversNamespace('PHPUnit\TestFixture\CoverageMetadata')]
final class TestWithCoversNamespace extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversTrait(CoveredTrait::class)]
final class TestWithCoversTrait extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversClass(CoveredClass::class)]
final class TestWithCoversClass extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversClassesThatExtendClass(CoveredClass::class)]
final class TestWithCoversClassesThatExtendClass extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversClassesThatImplementInterface(CoveredInterface::class)]
final class TestWithCoversClassesThatImplementInterface extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversMethod(CoveredClass::class, 'value')]
final class TestWithCoversMethod extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversFunction('PHPUnit\TestFixture\CoverageMetadata\covered_function')]
final class TestWithCoversFunction extends TestCase
{
    public function testOne(): void
    {
    }
}

#[CoversNothing]
final class TestWithCoversNothing extends TestCase
{
    public function testOne(): void
    {
    }
}

final class TestWithoutCoverageMetadata extends TestCase
{
    public function testOne(): void
    {
    }
}
