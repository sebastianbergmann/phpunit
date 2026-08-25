--TEST--
The tests cannot be selected by what changed when the list of paths that changed cannot be read
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-selection.xml';
$_SERVER['argv'][] = '--impacted-by-file';
$_SERVER['argv'][] = __DIR__ . '/_files/paths-that-are-not-listed-anywhere.txt';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.selection');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot read the files and directories that changed from %spaths-that-are-not-listed-anywhere.txt
