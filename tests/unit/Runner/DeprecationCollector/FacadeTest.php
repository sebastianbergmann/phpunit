<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\DeprecationCollector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Facade::class)]
#[Small]
#[Group('test-runner')]
final class FacadeTest extends TestCase
{
    public function testCollectorIsCreatedOnlyOnce(): void
    {
        $this->assertSame(Facade::collector(), Facade::collector());
    }

    public function testInitCreatesCollector(): void
    {
        Facade::init();

        $this->assertSame(Facade::collector(), Facade::collector());
    }

    public function testInitForIsolationCreatesCollector(): void
    {
        $property    = new ReflectionProperty(Facade::class, 'inIsolation');
        $inIsolation = $property->getValue();

        try {
            Facade::initForIsolation();

            $this->assertTrue($property->getValue());
            $this->assertSame(Facade::collector(), Facade::collector());
        } finally {
            $property->setValue(null, $inIsolation);
        }
    }

    public function testDeprecationsAreCollected(): void
    {
        $this->assertSame(Facade::collector()->deprecations(), Facade::deprecations());
    }

    public function testFilteredDeprecationsAreCollected(): void
    {
        $this->assertSame(Facade::collector()->filteredDeprecations(), Facade::filteredDeprecations());
    }
}
