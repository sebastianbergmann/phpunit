--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php declare(strict_types=1);
\some_unknown_function("Nothing to see here, move along");
// Some more lines
$a = 1;
?>
--EXPECT--
Nothing to see here, move along
