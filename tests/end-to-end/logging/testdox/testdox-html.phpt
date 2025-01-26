--TEST--
phpunit --testdox-html php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-html';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($output);

unlink($output);
--EXPECTF--
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
        <h2>Bank Account (PHPUnit\TestFixture\BankAccount)</h2>
        <ul>
            <li class="success">Balance is initially zero</li>
            <li class="success">Balance cannot become negative</li>
        </ul>
    </body>
</html>
