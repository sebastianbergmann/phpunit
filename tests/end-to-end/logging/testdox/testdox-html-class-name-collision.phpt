--TEST--
phpunit --testdox-html: Test classes whose prettified names collide are not merged into a single group
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-html';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../../end-to-end/testdox/_files/class-name-collision');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($output);

unlink($output);
--EXPECTF--
%A
        <h2>Foo</h2>
        <ul>
            <li class="success">From foo test</li>
        </ul>
        <h2>Foo</h2>
        <ul>
            <li class="success">From test foo test</li>
        </ul>
    </body>
</html>
