<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\IssueTriggerResolver;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefaultResolver implements Resolver
{
    /**
     * @param list<array{function: string, line?: int, file?: string, class?: class-string, type?: '->'|'::', args?: list<mixed>, object?: object}> $trace
     */
    public function resolve(array $trace, string $message): Resolution
    {
        $callee = null;

        if (isset($trace[0]['file']) && $trace[0]['file'] !== '') {
            $callee = $trace[0]['file'];
        }

        $caller = null;

        if (isset($trace[1]['file']) && $trace[1]['file'] !== '') {
            $caller = $trace[1]['file'];
        }

        return new Resolution($callee, $caller);
    }
}
