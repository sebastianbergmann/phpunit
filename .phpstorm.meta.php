<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPSTORM_META {

    override(
        \PHPUnit\Framework\TestCase::createMock(0),
        map(['' => '$0'])
    );

    override(
        \PHPUnit\Framework\TestCase::createStub(0),
        map(['' => '$0'])
    );

    override(
        \PHPUnit\Framework\TestCase::createConfiguredMock(0),
        map(['' => '$0'])
    );

    override(
        \PHPUnit\Framework\TestCase::createPartialMock(0),
        map(['' => '$0'])
    );

    override(
        \PHPUnit\Framework\TestCase::createTestProxy(0),
        map(['' => '$0'])
    );

    override(
        \PHPUnit\Framework\TestCase::getMockForAbstractClass(0),
        map(['' => '$0'])
    );
}
