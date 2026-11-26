--TEST--
phpunit --process-isolation runs tests in separate processes when the path of the directory for temporary files contains a single quote
--FILE--
<?php declare(strict_types=1);
$temporaryDirectory = __DIR__ . "/_files/temporary-directory-with-'-quote";

@mkdir($temporaryDirectory);

// The directory for temporary files is determined, and remembered, when it
// is first needed, so it is configured before anything else happens.
putenv('TMPDIR=' . $temporaryDirectory);
putenv('TMP=' . $temporaryDirectory);
putenv('TEMP=' . $temporaryDirectory);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/BankAccountTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
@rmdir(__DIR__ . "/_files/temporary-directory-with-'-quote");
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
