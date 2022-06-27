<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class DeprecatedPhpunitFeatureTest extends TestCase
{
    public function testDeprecatedPhpunitFeature(): void
    {
        $refProperty = new \ReflectionProperty(Assert::class, 'eventFacade');
        $refProperty->getValue($this)->emitter()->testTriggeredPhpunitDeprecation(
            $this->valueObjectForEvents(),
            'message'
        );

        $this->assertTrue(true);
    }
}
