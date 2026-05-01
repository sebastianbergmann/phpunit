<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\IssueTriggerResolver;

use PHPUnit\Runner\IssueTriggerResolver\Resolution;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;

final class ResolverA implements Resolver
{
    public function resolve(array $trace, string $message): ?Resolution
    {
        return null;
    }
}
