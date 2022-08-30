--TEST--
phpunit --testdox-html php://stdout ../../_files/BankAccountTest.php
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/4702 for details.
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-html';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

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
        <h2 id="PHPUnit\TestFixture\BankAccountTest">Bank Account (PHPUnit\TestFixture\BankAccount)</h2>
        <ul>
            <li style="color: #555753;">✓ Balance is initially zero</li>
            <li style="color: #555753;">✓ Balance cannot become negative</li>
            <li style="color: #555753;">✓ Balance cannot become negative</li>
        </ul>
    </body>
</html>
