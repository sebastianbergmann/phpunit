<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

class ExceptionWrapperTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetOriginalException(): void
    {
        $e       = new \BadFunctionCallException('custom class exception');
        $wrapper = new ExceptionWrapper($e);

        $this->assertInstanceOf(\BadFunctionCallException::class, $wrapper->getOriginalException());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetOriginalExceptionWithPrevious(): void
    {
        $e       = new \BadFunctionCallException('custom class exception', 0, new \Exception('previous'));
        $wrapper = new ExceptionWrapper($e);

        $this->assertInstanceOf(\BadFunctionCallException::class, $wrapper->getOriginalException());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNoOriginalExceptionInStacktrace(): void
    {
        $e       = new \BadFunctionCallException('custom class exception');
        $wrapper = new ExceptionWrapper($e);

        // Replace the only mention of "BadFunctionCallException" in wrapper
        $wrapper->setClassName('MyException');

        $data = \print_r($wrapper, 1);

        $this->assertNotContains(
            'BadFunctionCallException',
            $data,
            'Assert there is s no other BadFunctionCallException mention in stacktrace'
        );
    }
}
