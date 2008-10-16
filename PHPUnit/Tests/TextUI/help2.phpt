--TEST--
phpunit --help
--FILE--
<?php
$_SERVER['argv'][1] = '--help';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

Usage: phpunit [switches] UnitTest [UnitTest.php]
       phpunit [switches] <directory>

  --log-graphviz <file>    Log test execution in GraphViz markup.
  --log-json <file>        Log test execution in JSON format.
  --log-tap <file>         Log test execution in TAP format to file.
  --log-xml <file>         Log test execution in XML format to file.
  --log-metrics <file>     Write metrics report in XML format.
  --log-pmd <file>         Write violations report in PMD XML format.

  --coverage-html <dir>    Generate code coverage report in HTML format.
  --coverage-clover <file> Write code coverage data in Clover XML format.
  --coverage-source <dir>  Write code coverage / source data in XML format.

  --test-db-dsn <dsn>      DSN for the test database.
  --test-db-log-rev <rev>  Revision information for database logging.
  --test-db-prefix ...     Prefix that should be stripped from filenames.
  --test-db-log-info ...   Additional information for database logging.

  --story-html <file>      Write Story/BDD results in HTML format to file.
  --story-text <file>      Write Story/BDD results in Text format to file.

  --testdox-html <file>    Write agile documentation in HTML format to file.
  --testdox-text <file>    Write agile documentation in Text format to file.

  --filter <pattern>       Filter which tests to run.
  --group ...              Only runs tests from the specified group(s).
  --exclude-group ...      Exclude tests from the specified group(s).
  --list-groups            List available test groups.

  --loader <loader>        TestSuiteLoader implementation to use.
  --repeat <times>         Runs the test(s) repeatedly.

  --story                  Report test execution progress in Story/BDD format.
  --tap                    Report test execution progress in TAP format.
  --testdox                Report test execution progress in TestDox format.

  --no-syntax-check        Disable syntax check of test source files.
  --stop-on-failure        Stop execution upon first error or failure.
  --colors                 Use colors in output.
  --verbose                Output more verbose information.
  --wait                   Waits for a keystroke after each test.

  --skeleton-class         Generate Unit class for UnitTest in UnitTest.php.
  --skeleton-test          Generate UnitTest class for Unit in Unit.php.

  --help                   Prints this usage information.
  --version                Prints the version and exits.

  --bootstrap <file>       A "bootstrap" PHP file that is run before the tests.
  --configuration <file>   Read configuration from XML file.
  -d key[=value]           Sets a php.ini value.

