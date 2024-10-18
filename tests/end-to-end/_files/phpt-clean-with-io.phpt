--TEST--
PHPT test with a CLEAN section with IO side-effect runs in main process
--FILE--
<?php declare(strict_types=1);
file_put_contents(__DIR__.'/phpt-clean-with-io-tmp.file', 'Hello tmp file!');
print 'success';
--EXPECT--
success
--CLEAN--
<?php declare(strict_types=1);
@unlink(__DIR__.'/phpt-clean-with-io-tmp.file');
