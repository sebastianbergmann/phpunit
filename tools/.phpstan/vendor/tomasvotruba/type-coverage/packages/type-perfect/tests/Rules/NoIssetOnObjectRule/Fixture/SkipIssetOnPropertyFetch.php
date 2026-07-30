<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Fixture;

use Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Source\Foo;

class SkipIssetOnPropertyFetch
{
	private Foo $foo;

	public function sayHello(): void
	{
		$this->initializeFoo();

		$this->foo->sayHello();
	}

	private function initializeFoo(): void
	{
		if (!isset($this->foo))
		{
			$this->foo = new Foo();
		}
	}
}