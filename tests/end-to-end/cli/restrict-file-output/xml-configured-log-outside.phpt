--TEST--
An output path configured in the XML configuration file is checked against --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/xml-log-outside/phpunit.xml';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/xml-log-outside';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sxml-log-outside, the directory that --restrict-file-output allows writing to:

  %srestrict-file-output%sjunit.xml (JUnit XML log)
