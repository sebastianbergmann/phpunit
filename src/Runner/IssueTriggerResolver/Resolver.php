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
 */
interface Resolver
{
    /**
     * Return null to defer to the next resolver in the chain.
     *
     * @param list<array{file?: string, line?: int, class?: class-string, function?: string, type?: string, args?: list<mixed>, object?: object}> $trace
     */
    public function resolve(array $trace, string $message): ?Resolution;
}
