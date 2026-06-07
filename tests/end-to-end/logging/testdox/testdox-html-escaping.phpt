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
%A
        <h2>&lt;script&gt;alert(1)&lt;/script&gt;</h2>
        <ul>
            <li class="success">&lt;b&gt;&quot;x&quot; &amp; &#039;y&#039;&lt;/b&gt;</li>
            <li class="success">Two with data set &quot;&lt;img src=x onerror=alert(2)&gt;&quot;</li>
        </ul>
    </body>
</html>
