<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class HtmlRenderer
{
    /**
     * @var string
     */
    private const PAGE_HEADER = <<<'EOT'
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2em;
                background-color: #ffffff;
                color: #000000;
            }

            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-size: 2em;
            }

            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 3em;
            }

            ul {
                list-style: none;
                margin-bottom: 1em;
            }
        </style>
    </head>
    <body>
EOT;

    /**
     * @var string
     */
    private const CLASS_HEADER = <<<'EOT'

        <h2 id="%s">%s</h2>
        <ul>

EOT;

    /**
     * @var string
     */
    private const CLASS_FOOTER = <<<'EOT'
        </ul>
EOT;

    /**
     * @var string
     */
    private const PAGE_FOOTER = <<<'EOT'

    </body>
</html>
EOT;

    /**
     * @psalm-param array<class-string,array{test: TestMethod, duration: Duration, status: TestStatus, testDoubles: list<class-string|trait-string>}> $tests
     */
    public function render(array $tests): string
    {
        $buffer     = self::PAGE_HEADER;
        $prettifier = new NamePrettifier;

        foreach ($tests as $className => $_tests) {
            $buffer .= sprintf(
                self::CLASS_HEADER,
                $className,
                $prettifier->prettifyTestClass($className)
            );

            foreach ($_tests as $test) {
                $buffer .= sprintf(
                    "            <li style=\"color: %s;\">%s %s</li>\n",
                    $test['status']->isSuccess() ? '#555753' : '#ef2929',
                    $test['status']->isSuccess() ? '✓' : '❌',
                    $prettifier->prettifyTestMethod($test['test']->methodName())
                );
            }

            $buffer .= self::CLASS_FOOTER;
        }

        return $buffer . self::PAGE_FOOTER;
    }
}
