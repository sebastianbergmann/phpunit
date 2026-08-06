--TEST--
ChildProcessOutputCollector prepends output that was written to STDOUT when STDOUT is rewindable
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

use PHPUnit\Framework\TestRunner\ChildProcessOutputCollector;
use PHPUnit\TestFixture\Success;

fwrite(STDOUT, 'output written to STDOUT');

fwrite(STDOUT, ChildProcessOutputCollector::collect(new Success('testOne')));
--EXPECT--
output written to STDOUToutput written to STDOUT
