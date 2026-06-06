<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationFilter;

use function str_contains;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Runner\DeprecationFilter\Filter;

final class IgnoringDeprecationFilter implements Filter
{
    public function ignores(string $message, string $file, int $line, IssueTrigger $trigger): bool
    {
        return str_contains($message, 'please ignore this deprecation');
    }
}
