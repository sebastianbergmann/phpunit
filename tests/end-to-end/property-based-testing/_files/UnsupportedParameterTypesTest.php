<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use Closure;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/AnInterface.php';

require_once __DIR__ . '/AnAbstractClass.php';

require_once __DIR__ . '/AClassWithoutAPublicConstructor.php';

final class UnsupportedParameterTypesTest extends TestCase
{
    #[Property]
    public function testInterface(AnInterface $value): void
    {
    }

    #[Property]
    public function testAbstractClass(AnAbstractClass $value): void
    {
    }

    #[Property]
    public function testClassWithoutPublicConstructor(AClassWithoutAPublicConstructor $value): void
    {
    }

    #[Property]
    public function testObject(object $value): void
    {
    }

    #[Property]
    public function testCallable(callable $value): void
    {
    }

    #[Property]
    public function testClosure(Closure $value): void
    {
    }

    #[Property]
    public function testIterable(iterable $value): void
    {
    }

    /**
     * @param resource $value
     */
    #[Property]
    public function testResource($value): void
    {
    }

    /**
     * @template T
     *
     * @param T $value
     */
    #[Property]
    public function testTemplateType(mixed $value): void
    {
    }
}
