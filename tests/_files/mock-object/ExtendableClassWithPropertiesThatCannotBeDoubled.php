<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\MockObject;

class ExtendableClassWithPropertiesThatCannotBeDoubled
{
    public static string $staticProperty = 'value';
    public readonly string $readonlyProperty;
    final public string $finalProperty;
    public $untypedProperty;
    protected string $protectedProperty = 'value';

    public function __construct()
    {
        $this->readonlyProperty = 'value';
        $this->finalProperty    = 'value';
    }
}
