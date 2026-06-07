--TEST--
phpunit --testdox-html: HTML metacharacters in prettified class names, method names, and data-set names are escaped
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-html';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../../end-to-end/testdox/_files/html-escaping');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($output);

unlink($output);
--EXPECTF--
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
        <header class="summary">
            <h1>Test Documentation</h1>
            <p class="counts"><span class="passed">2 passed</span></p>
        </header>
        <main>

            <section class="test-class">
                <h2>&lt;script&gt;alert(1)&lt;/script&gt;</h2>
                <ul>
                    <li class="success">&lt;b&gt;&quot;x&quot; &amp; &#039;y&#039;&lt;/b&gt;</li>
                    <li class="success">Two with data set &quot;&lt;img src=x onerror=alert(2)&gt;&quot;</li>
                </ul>
            </section>
        </main>
    </body>
</html>