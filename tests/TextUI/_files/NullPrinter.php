<?php
namespace PHPUnit\Test;

use PHPUnit\Util\Printer;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

final class NullPrinter extends Printer implements TestListener
{
    use TestListenerDefaultImplementation;
}
