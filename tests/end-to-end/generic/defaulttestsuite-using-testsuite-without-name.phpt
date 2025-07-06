--TEST--
phpunit --configuration=__DIR__.'/../../_files/configuration.testsuite_no_name.xml'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../../_files/configuration.testsuite_no_name.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot load XML configuration file %sconfiguration.testsuite_no_name.xml because it has validation errors:

  Line 3:
  - Element 'testsuite': The attribute 'name' is required but missing.
