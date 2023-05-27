<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use function sprintf;
use PHPUnit\Framework\TestResult;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class HtmlResultPrinter extends ResultPrinter
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
                font-family: Source SansSerif Pro, Arial, sans-serif;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2rem;
                background-color: #fff;
                color: #000;
            }

            body > ul > li {
                font-size: larger;
            }

            h2 {
                font-size: larger;
                text-decoration-line: underline;
                text-decoration-thickness: 2px;
                margin: 0;
                padding: 0.5rem 0;
            }

            ul {
                list-style: none;
                margin: 0 0 2rem;
                padding: 0 0 0 1rem;
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

    public function printResult(TestResult $result): void
    {
    }

    /**
     * Handler for 'start run' event.
     */
    protected function startRun(): void
    {
        $this->write(self::PAGE_HEADER);
    }

    /**
     * Handler for 'start class' event.
     */
    protected function startClass(string $name): void
    {
        $this->write(
            sprintf(
                self::CLASS_HEADER,
                $this->currentTestClassPrettified,
            ),
        );
    }

    /**
     * Handler for 'on test' event.
     */
    protected function onTest(string $name, bool $success = true): void
    {
        $this->write(
            sprintf(
                "            <li class=\"%s\">%s</li>\n",
                $success ? 'success' : 'defect',
                $name,
            ),
        );
    }

    /**
     * Handler for 'end class' event.
     */
    protected function endClass(string $name): void
    {
        $this->write(self::CLASS_FOOTER);
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun(): void
    {
        $this->write(self::PAGE_FOOTER);
    }
}
