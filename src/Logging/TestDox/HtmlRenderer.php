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

use function sprintf;
use PHPUnit\TestRunner\TestResult\TestResult;

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

    public function render(TestResult $result): string
    {
        $buffer     = self::PAGE_HEADER;
        $prettifier = new NamePrettifier;

        foreach ($result->testMethodsGroupedByClassAndSortedByLine() as $className => $tests) {
            $buffer .= sprintf(
                self::CLASS_HEADER,
                $className,
                $prettifier->prettifyTestClass($className)
            );

            foreach ($tests as $test) {
                $buffer .= sprintf(
                    "            <li style=\"color: %s;\">%s %s</li>\n",
                    $test->status()->isSuccess() ? '#555753' : '#ef2929',
                    $test->status()->isSuccess() ? '✓' : '❌',
                    $prettifier->prettifyTestMethod($test->methodName())
                );
            }

            $buffer .= self::CLASS_FOOTER;
        }

        return $buffer . self::PAGE_FOOTER;
    }
}
