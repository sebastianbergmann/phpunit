<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;


/**
 * The exception that is thrown when an image error occurs.
 */
class ImageException extends \Exception
{
}


/**
 * The exception that indicates invalid image file.
 */
class UnknownImageFileException extends ImageException
{
}


/**
 * The exception that indicates error of JSON encoding/decoding.
 */
class JsonException extends \JsonException
{
}


/**
 * The exception that indicates error of the last Regexp execution.
 */
class RegexpException extends \Exception
{
}


/**
 * The exception that indicates assertion error.
 */
class AssertionException extends \Exception
{
}
