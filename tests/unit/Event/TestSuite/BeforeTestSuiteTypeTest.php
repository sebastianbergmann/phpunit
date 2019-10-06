<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\AbstractTypeTestCase;
use PHPUnit\Event\Type;

/**
 * @covers \PHPUnit\Event\Test\BeforeTestSuiteType
 */
final class BeforeTestSuiteTypeTest extends AbstractTypeTestCase
{
    protected function asString(): string
    {
        return 'before-test-suite';
    }

    protected function type(): Type
    {
        return new BeforeTestSuiteType();
    }
}
