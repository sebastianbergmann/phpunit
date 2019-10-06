<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

/**
 * @covers \PHPUnit\Event\Test\AfterTestSuiteType
 */
final class NamedTypeTest extends AbstractTypeTestCase
{
    protected function asString(): string
    {
        return 'name';
    }

    protected function type(): Type
    {
        return new NamedType('name');
    }
}
