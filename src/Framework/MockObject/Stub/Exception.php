<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
use SebastianBergmann\Exporter\Exporter;

/**
 * Stubs a method by raising a user-defined exception.
 */
class Exception implements Stub
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function invoke(Invocation $invocation): void
    {
        throw $this->exception;
    }

    public function toString(): string
    {
        $exporter = new Exporter;

        return \sprintf(
            'raise user-specified exception %s',
            $exporter->export($this->exception)
        );
    }
}
