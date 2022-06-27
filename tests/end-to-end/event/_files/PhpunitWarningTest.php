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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class PhpunitWarningTest extends TestCase
{
    public function testPhpunitWarning(): void
    {
        $refProperty = new ReflectionProperty(Assert::class, 'eventFacade');
        $refProperty->getValue($this)->emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'message'
        );

        $this->assertTrue(true);
    }
}
