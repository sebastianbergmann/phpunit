--TEST--
PHPT runner should support ENV section
--ENV--
FOO=bar
--FILE--
<?php declare(strict_types=1);
if (isset($_SERVER['FOO'])) {
    \var_dump($_SERVER['FOO']);
}
--EXPECTF_EXTERNAL--
_files/phpt-env.expected.txt
