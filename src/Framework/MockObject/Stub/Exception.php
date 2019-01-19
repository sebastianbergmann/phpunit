<?php declare(strict_types=1);
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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Exception implements Stub
{
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @throws \Throwable
     */
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
