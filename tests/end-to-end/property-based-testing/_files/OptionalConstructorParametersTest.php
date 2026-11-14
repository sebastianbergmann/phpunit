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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/DomainEvent.php';

require_once __DIR__ . '/Notification.php';

require_once __DIR__ . '/NullableConstructorParameterWithoutDefaultValue.php';

final class OptionalConstructorParametersTest extends TestCase
{
    #[Property]
    public function testDefaultValueIsUsedForParameterForWhichNoGeneratorCanBeDerived(DomainEvent $event): void
    {
        $this->assertNull($event->previous);
        $this->assertNull($event->callback);
    }

    #[Property]
    public function testValuesAreGeneratedForParameterWithDefaultValueForWhichGeneratorCanBeDerived(Notification $notification): void
    {
        $this->assertNull($notification->formatter);
        $this->assertLessThan(3, $notification->priority);
    }

    #[Property]
    public function testNullableParameterWithoutDefaultValue(NullableConstructorParameterWithoutDefaultValue $value): void
    {
    }
}
