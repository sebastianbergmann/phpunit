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

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;
use function htmlspecialchars;
use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class HtmlRenderer
{
    private const string PAGE_HEADER = <<<'EOT'
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Test Documentation</title>
        <style>
            :root {
                color-scheme: light dark;

                --background: light-dark(#f4f5f7, #16181d);
                --card-background: light-dark(#ffffff, #1f2228);
                --border: light-dark(#e2e4e8, #30343c);
                --text: light-dark(#1a1a1a, #e6e8eb);
                --muted: light-dark(#6b7280, #9aa1ab);
                --shadow: light-dark(0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.4));

                --passed-background: light-dark(#d6e6f2, #1e3550);
                --failed-background: light-dark(#fad4c0, #4a2a10);
            }

            *, *::before, *::after {
                box-sizing: border-box;
            }

            body {
                text-rendering: optimizeLegibility;
                font-family: Source SansSerif Pro, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                line-height: 1.5;
                margin: 0;
                padding: 2.5rem 1rem;
                background-color: var(--background);
                color: var(--text);
            }

            .summary,
            main {
                max-width: 52rem;
                margin: 0 auto;
            }

            .summary {
                margin-bottom: 1.5rem;
            }

            .summary h1 {
                font-size: 1.5rem;
                font-weight: 600;
                margin: 0 0 0.25rem;
            }

            .counts {
                margin: 0;
                color: var(--muted);
            }

            .counts .passed,
            .counts .failed {
                display: inline-block;
                padding: 0.1rem 0.5rem;
                border-radius: 0.25rem;
                color: var(--text);
                font-weight: 600;
            }

            .counts .passed {
                background-color: var(--passed-background);
            }

            .counts .failed {
                background-color: var(--failed-background);
            }

            .test-class {
                background-color: var(--card-background);
                border: 1px solid var(--border);
                border-radius: 0.5rem;
                box-shadow: var(--shadow);
                padding: 1rem 1.25rem;
                margin-bottom: 1rem;
            }

            .test-class h2 {
                font-size: 1.1rem;
                font-weight: 600;
                margin: 0 0 0.5rem;
                padding-bottom: 0.5rem;
                border-bottom: 1px solid var(--border);
            }

            ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            li {
                position: relative;
                margin-bottom: 0.25rem;
                padding: 0.25rem 0.5rem 0.25rem 1.9rem;
                border-radius: 0.25rem;
            }

            li::before {
                position: absolute;
                left: 0.5rem;
            }

            li.success {
                background-color: var(--passed-background);
            }

            li.success::before {
                content: '✓';
            }

            li.defect {
                background-color: var(--failed-background);
            }

            li.defect::before {
                content: '✗';
            }
        </style>
    </head>
    <body>
EOT;
    private const string SUMMARY = <<<'EOT'

        <header class="summary">
            <h1>Test Documentation</h1>
            <p class="counts">%s</p>
        </header>
        <main>

EOT;
    private const string CLASS_HEADER = <<<'EOT'

            <section class="test-class">
                <h2>%s</h2>
                <ul>

EOT;
    private const string CLASS_FOOTER = <<<'EOT'
                </ul>
            </section>

EOT;
    private const string PAGE_FOOTER = <<<'EOT'
        </main>
    </body>
</html>
EOT;

    /**
     * @param array<class-string, TestResultCollection> $tests
     */
    public function render(array $tests): string
    {
        $successful = 0;
        $defective  = 0;
        $classes    = '';

        foreach ($tests as $_tests) {
            $list = $_tests->asArray();

            if ($list === []) {
                continue;
            }

            $classes .= sprintf(
                self::CLASS_HEADER,
                htmlspecialchars(
                    $list[0]->test()->testDox()->prettifiedClassName(),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                ),
            );

            foreach ($this->reduce($_tests) as $prettifiedMethodName => $outcome) {
                if ($outcome === 'success') {
                    $successful++;
                } else {
                    $defective++;
                }

                $classes .= sprintf(
                    "                    <li class=\"%s\">%s</li>\n",
                    $outcome,
                    htmlspecialchars($prettifiedMethodName, ENT_QUOTES | ENT_SUBSTITUTE),
                );
            }

            $classes .= self::CLASS_FOOTER;
        }

        return self::PAGE_HEADER .
               sprintf(self::SUMMARY, $this->summarize($successful, $defective)) .
               $classes .
               self::PAGE_FOOTER;
    }

    private function summarize(int $successful, int $defective): string
    {
        $passed = sprintf(
            '<span class="passed">%d passed</span>',
            $successful,
        );

        if ($defective === 0) {
            return $passed;
        }

        return sprintf(
            '%s · <span class="failed">%d failed</span>',
            $passed,
            $defective,
        );
    }

    /**
     * @return array<string, 'defect'|'success'>
     */
    private function reduce(TestResultCollection $tests): array
    {
        $result = [];

        foreach ($tests as $test) {
            $prettifiedMethodName = $test->test()->testDox()->prettifiedMethodName();

            if (!isset($result[$prettifiedMethodName])) {
                $result[$prettifiedMethodName] = $test->status()->isSuccess() ? 'success' : 'defect';

                continue;
            }

            if ($test->status()->isSuccess()) {
                continue;
            }

            $result[$prettifiedMethodName] = 'defect';
        }

        return $result;
    }
}
