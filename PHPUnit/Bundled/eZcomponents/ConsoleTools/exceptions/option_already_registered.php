<?php
/**
 * File containing the ezcConsoleOptionAlreadyRegisteredException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * The option name you tried to register is already in use.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionAlreadyRegisteredException extends ezcConsoleException
{
    function __construct( $name )
    {
        parent::__construct( "An option with the name <{$name}> is already registered." );
    }
}

?>
