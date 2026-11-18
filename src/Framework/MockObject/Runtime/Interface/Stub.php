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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * This interface is not meant to be implemented outside of PHPUnit: a test
 * stub is created by PHPUnit, for instance using TestCase::createStub().
 *
 * @phpstan-sealed MockObject|StubInternal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Stub
{
    public function method(Constraint|PropertyHook|string $constraint): InvocationStubber;
}
