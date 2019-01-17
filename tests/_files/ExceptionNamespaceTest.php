<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace My\Space;

class ExceptionNamespaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Exception message
     *
     * @var string
     */
    public const ERROR_MESSAGE = 'Exception namespace message';

    /**
     * Exception code
     *
     * @var int
     */
    public const ERROR_CODE = 200;

    /**
     * @expectedException Class
     * @expectedExceptionMessage My\Space\ExceptionNamespaceTest::ERROR_MESSAGE
     * @expectedExceptionCode My\Space\ExceptionNamespaceTest::ERROR_CODE
     */
    public function testConstants(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode My\Space\ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT
     * @expectedExceptionMessage My\Space\ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT
     */
    public function testUnknownConstants(): void
    {
    }
}
