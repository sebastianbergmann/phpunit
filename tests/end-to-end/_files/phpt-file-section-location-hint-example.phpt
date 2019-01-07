--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php
\some_unknown_function("Nothing to see here, move along");
// Some more lines
$a = 1;
?>
--EXPECT--
Nothing to see here, move along
