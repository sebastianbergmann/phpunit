--TEST--
Another PHPT test executed by a parallel worker
--FILE--
<?php declare(strict_types=1);
print 'the other phpt test ran in a worker';
--EXPECT--
the other phpt test ran in a worker
