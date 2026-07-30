<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\Source;

class Foo
{
	public function sayHello(): void
	{
		echo 'Hello';
	}
}