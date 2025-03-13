<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;


/**
 * ReflectionMethod preserving the original class name.
 * @internal
 */
final class ReflectionMethod extends \ReflectionMethod
{
	private \ReflectionClass $originalClass;


	public function __construct(object|string $objectOrMethod, ?string $method = null)
	{
		if (is_string($objectOrMethod) && str_contains($objectOrMethod, '::')) {
			[$objectOrMethod, $method] = explode('::', $objectOrMethod, 2);
		}
		parent::__construct($objectOrMethod, $method);
		$this->originalClass = new \ReflectionClass($objectOrMethod);
	}


	public function getOriginalClass(): \ReflectionClass
	{
		return $this->originalClass;
	}
}
