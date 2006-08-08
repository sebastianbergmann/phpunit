<?php
/**
 * File containing the ezcConsoleOptionTooManyValuesException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * An option that expects only a single value was submitted with multiple values.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionTooManyValuesException extends ezcConsoleOptionException
{
    function __construct( ezcConsoleOption $option )
    {
        parent::__construct( "The option <{$option->long}> expects a single value, but multiple were submitted." );
    }
}

?>
