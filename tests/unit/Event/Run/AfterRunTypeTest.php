<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Run;

use PHPUnit\Event\AbstractTypeTestCase;
use PHPUnit\Event\Type;

/**
 * @covers \PHPUnit\Event\Run\AfterRunType
 */
final class AfterRunTypeTest extends AbstractTypeTestCase
{
    protected function asString(): string
    {
        return 'after-run';
    }

    protected function type(): Type
    {
        return new AfterRunType();
    }
}
