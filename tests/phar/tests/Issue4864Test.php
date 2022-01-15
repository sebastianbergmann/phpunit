<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar;

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../build/config/scoper_replace_function.php';

/**
 * @coversNothing
 */
final class Issue4864Test extends TestCase
{
    /**
     * @dataProvider contentProvider
     */
    public function testReplacesInvalidUsages(string $scopedContent, string $expectedCorrectedContent): void
    {
        $actual = scoper_replace_function('PhpScoperPrefix', $scopedContent, 'xdebug_info');

        self::assertSame($expectedCorrectedContent, $actual);
    }

    public static function contentProvider(): iterable
    {
        yield 'function usage' => [
            <<<'PHP'
             PhpScoperPrefix\xdebug_info();
             \PhpScoperPrefix\xdebug_info();
            PHP,
            <<<'PHP'
             xdebug_info();
             \xdebug_info();
            PHP,
        ];

        yield 'import statement' => [
            <<<'PHP'
            use function PhpScoperPrefix\xdebug_info;
            use function \PhpScoperPrefix\xdebug_info;
            PHP,
            <<<'PHP'
            use function xdebug_info;
            use function \xdebug_info;
            PHP,
        ];

        yield 'function name check' => [
            <<<'PHP'
            if (function_exists('PhpScoperPrefix\xdebug_info')) {}
            if (function_exists('\PhpScoperPrefix\xdebug_info')) {}
            if (function_exists("PhpScoperPrefix\xdebug_info")) {}
            if (function_exists("\PhpScoperPrefix\xdebug_info")) {}
            PHP,
            <<<'PHP'
            if (function_exists('xdebug_info')) {}
            if (function_exists('\xdebug_info')) {}
            if (function_exists("xdebug_info")) {}
            if (function_exists("\xdebug_info")) {}
            PHP,
        ];
    }
}
