<?php
/**
 * File containing the ezcBaseFileException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseFileException is the exception from which all file related exceptions
 * inherit.
 *
 * @package Base
 * @version 1.1
 */
abstract class ezcBaseFileException extends ezcBaseException
{
    const READ    = 1;
    const WRITE   = 2;
    const EXECUTE = 4;
    const CHANGE  = 8;
    const REMOVE  = 16;
}
?>
