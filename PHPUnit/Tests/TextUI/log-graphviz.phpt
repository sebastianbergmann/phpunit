--TEST--
phpunit --log-graphviz php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--log-graphviz';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...digraph G {
graph [ overlap="scale",splines="true",sep=".1",fontsize="8" ];
"BankAccountTest" [ color="green" ];
subgraph "cluster_BankAccountTest" {
label="";
"testBalanceIsInitiallyZero" [ color="green" ];
"testBalanceCannotBecomeNegative" [ color="green" ];
"testBalanceCannotBecomeNegative2" [ color="green" ];
}
"BankAccountTest" -> "testBalanceIsInitiallyZero";
"BankAccountTest" -> "testBalanceCannotBecomeNegative";
"BankAccountTest" -> "testBalanceCannotBecomeNegative2";
}


Time: %i seconds

OK (3 tests, 3 assertions)
