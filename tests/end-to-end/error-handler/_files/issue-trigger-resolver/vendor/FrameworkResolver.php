<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolver;

use PHPUnit\Runner\IssueTriggerResolver\Resolution;
use PHPUnit\Runner\IssueTriggerResolver\Resolver;
use function str_contains;

final class FrameworkResolver implements Resolver
{
    /**
     * @param list<array{file?: string, line?: int, class?: class-string, function?: string, type?: string}> $trace
     */
    public function resolve(array $trace, string $message): ?Resolution
    {
        if (isset($trace[0]['file']) && str_contains($trace[0]['file'], 'Framework.php')) {
            return new Resolution(
                $trace[1]['file'] ?? null,
                $trace[2]['file'] ?? null,
            );
        }

        return null;
    }
}
