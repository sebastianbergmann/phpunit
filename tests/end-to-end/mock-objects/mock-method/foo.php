<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Foo
{
    public function bar()
    {
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$class      = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\MockMethod::fromReflection(
    $class->getMethod('bar'),
    true,
    true
);

$code = $mockMethod->generateCode();

print $code;
