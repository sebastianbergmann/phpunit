--TEST--
PHPT runner supports ARGS section
--ARGS--
help
--FILE--
<?php
if ($argc > 0 && $argv[1] == 'help') {
    print 'Help';
}
?>
--EXPECT--
Help
