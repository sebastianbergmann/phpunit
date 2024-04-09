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

use function version_compare;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Facade as EventFacade;

/**
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait DoubledCloneMethod
{
    public function __clone(): void
    {
        if (version_compare('8.3.0', PHP_VERSION, '>')) {
            EventFacade::emitter()->testTriggeredPhpunitError(
                TestMethodBuilder::fromCallStack(),
                'Cloning test double objects requires PHP 8.3',
            );

            return;
        }

        $this->__phpunit_state = clone $this->__phpunit_state;

        $this->__phpunit_state()->cloneInvocationHandler();
    }

    abstract public function __phpunit_state(): TestDoubleState;
}
