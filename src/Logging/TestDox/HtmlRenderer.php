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
                margin-left: 2rem;
                background-color: #fff;
                color: #000;
            }

            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-size: larger;
            }

            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: larger;
                margin: 0;
                padding: 0.5rem 0;
            }

            ul {
                list-style: none;
                margin: 0;
                padding: 0;
                margin-bottom: 2rem;
                padding-left: 1rem;
                text-indent: -1rem;
            }

            .success:before {
                color: #4e9a06;
                content: '✓';
                padding-right: 0.5rem;
            }

            .defect {
                color: #a40000;
            }

            .defect:before {
                color: #a40000;
                content: '✗';
                padding-right: 0.5rem;
            }
        </style>
    </head>
    <body>
EOT;

    /**
     * @var string
     */
    private const CLASS_HEADER = <<<'EOT'

        <h2>%s</h2>
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
     * @psalm-param array<string, TestResultCollection> $tests
     */
    public function render(array $tests): string
    {
        $buffer = self::PAGE_HEADER;

        foreach ($tests as $prettifiedClassName => $_tests) {
            $buffer .= sprintf(
                self::CLASS_HEADER,
                $prettifiedClassName
            );

            foreach ($_tests as $test) {
                $buffer .= sprintf(
                    "            <li class=\"%s\">%s</li>\n",
                    $test->status()->isSuccess() ? 'success' : 'defect',
                    $test->test()->testDox()->prettifiedMethodName()
                );
            }

            $buffer .= self::CLASS_FOOTER;
        }

        return $buffer . self::PAGE_FOOTER;
    }
}
