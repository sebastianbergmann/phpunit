<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope;

use PHPUnit\Runner\IssueTriggerResolver\Resolution;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;

final class SkippingResolver implements Resolver
{
    public function resolve(array $trace, string $message): ?Resolution
    {
        return null;
    }
}
