<?php
/**
 * File containing the ezcConsoleOptionMissingValueException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * An option which expects a value was submitted without.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionMissingValueException extends ezcConsoleOptionException
{
    function __construct( ezcConsoleOption $option )
    {
        parent::__construct( "The option <{$option->long}> expects a value, but none was submitted." );
    }
}

?>
