<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foo;

class CoveredParentClass
{
    public function publicMethod()
    {
        $this->protectedMethod();
    }

    protected function protectedMethod()
    {
        $this->privateMethod();
    }

    private function privateMethod()
    {
    }
}

class CoveredClass extends CoveredParentClass
{
    public function publicMethod()
    {
        parent::publicMethod();
        $this->protectedMethod();
    }

    protected function protectedMethod()
    {
        parent::protectedMethod();
        $this->privateMethod();
    }

    private function privateMethod()
    {
    }
}
