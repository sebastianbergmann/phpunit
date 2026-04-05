--TEST--
Shutdown Handler: no output when message is reset before shutdown
--FILE--
<?php declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

PHPUnit\Runner\ShutdownHandler::setMessage('This should not be printed');
PHPUnit\Runner\ShutdownHandler::resetMessage();

print 'done' . PHP_EOL;
--EXPECT--
done
