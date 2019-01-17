--TEST--
PHPT runner supports ARGS section
--ARGS--
help
--FILE--
<?php declare(strict_types=1);
if ($argc > 0 && $argv[1] == 'help') {
    print 'Help';
}
?>
--EXPECT--
Help
