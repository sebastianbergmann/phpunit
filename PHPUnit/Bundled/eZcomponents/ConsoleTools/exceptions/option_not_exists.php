<?php
/**
 * File containing the ezcConsoleOptionNotExistsException
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * The requested option is not registered.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionNotExistsException extends ezcConsoleOptionException
{
    function __construct( $name )
    {
        parent::__construct( "The referenced parameter <{$name}> is not registered." );
    }
}
?>
