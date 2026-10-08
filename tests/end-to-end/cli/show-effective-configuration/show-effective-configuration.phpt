--TEST--
phpunit --show-effective-configuration neither includes the bootstrap script, nor applies the PHP settings, nor loads extensions or test files
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--show-effective-configuration';

require_once __DIR__ . '/../../../bootstrap.php';

register_shutdown_function(
    static function (): void
    {
        print PHP_EOL;
        print 'Bootstrap script was included: ' . var_export(defined('PHPUNIT_SHOW_EFFECTIVE_CONFIGURATION_BOOTSTRAP'), true) . PHP_EOL;
        print 'Test file was loaded: ' . var_export(class_exists(PHPUnit\TestFixture\ShowEffectiveConfiguration\ExampleTest::class, false), true) . PHP_EOL;
        print 'Extension was loaded: ' . var_export(class_exists(PHPUnit\TestFixture\ShowEffectiveConfiguration\Extension::class, false), true) . PHP_EOL;
        print 'Constant is defined: ' . var_export(defined('PHPUNIT_SHOW_EFFECTIVE_CONFIGURATION_CONSTANT'), true) . PHP_EOL;
        print 'Environment variable is set: ' . var_export(getenv('PHPUNIT_SHOW_EFFECTIVE_CONFIGURATION_ENV') !== false, true) . PHP_EOL;
    },
);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Settings:
%A
  bootstrap: %s_files%ebootstrap.php
%A
  extensionBootstrappers:
    - className: PHPUnit\TestFixture\ShowEffectiveConfiguration\Extension
      parameters:
        key: value
%A
  failOnRisky: true
%A
  php:
    constants:
      - name: PHPUNIT_SHOW_EFFECTIVE_CONFIGURATION_CONSTANT
        value: value
%A
    envVariables:
      - force: false
        name: PHPUNIT_SHOW_EFFECTIVE_CONFIGURATION_ENV
        value: value
%A
Test files:
  unit:
    - %s_files%etests%eunit%eExampleTest.php
  integration:
    - %s_files%etests%eintegration%eIntegrationTest.php (groups: slow, database)

Source files:
  - %s_files%esrc%eExample.php
  - %s_files%esrc%eGenerated.php

Source files excluded from code coverage:
  - %s_files%esrc%eGenerated.php

PHAR extensions:
  - %s_files%eextensions%eextension.phar

Bootstrap script was included: false
Test file was loaded: false
Extension was loaded: false
Constant is defined: false
Environment variable is set: false
