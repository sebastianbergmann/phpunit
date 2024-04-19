<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhpJobRunnerRegistry
{
    private static ?PhpJobRunner $runner = null;

    /**
     * @psalm-return array{stdout: string, stderr: string}
     */
    public static function run(PhpJob $job): array
    {
        if (self::$runner === null) {
            self::$runner = new DefaultPhpJobRunner;
        }

        return self::$runner->run($job);
    }

    public static function set(PhpJobRunner $runner): void
    {
        self::$runner = $runner;
    }
}
