<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use PHPUnit\Event\Event;
use PHPUnit\Event\Tracer\Tracer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PlainTextTracer implements Tracer
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function trace(Event $event): void
    {
        file_put_contents(
            $this->path,
            $event->asString() . PHP_EOL,
            FILE_APPEND|LOCK_EX
        );
    }
}
