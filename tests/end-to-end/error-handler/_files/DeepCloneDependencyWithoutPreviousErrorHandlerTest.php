<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use const E_USER_NOTICE;
use function trigger_error;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\TestCase;

final class ValueObjectTriggeringNoticeWhenCloned
{
    public function __clone(): void
    {
        trigger_error('notice from __clone()', E_USER_NOTICE);
    }
}

final class DeepCloneDependencyWithoutPreviousErrorHandlerTest extends TestCase
{
    public function testProducer(): ValueObjectTriggeringNoticeWhenCloned
    {
        $this->assertTrue(true);

        return new ValueObjectTriggeringNoticeWhenCloned;
    }

    #[DependsUsingDeepClone('testProducer')]
    public function testConsumer(ValueObjectTriggeringNoticeWhenCloned $value): void
    {
        $this->assertTrue(true);
    }
}
