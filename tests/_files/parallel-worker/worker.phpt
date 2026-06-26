--TEST--
A PHPT test executed by a parallel worker
--FILE--
<?php declare(strict_types=1);
print 'the phpt test ran in a worker';
--EXPECT--
the phpt test ran in a worker
