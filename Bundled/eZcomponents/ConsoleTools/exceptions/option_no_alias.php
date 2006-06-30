<?php
/**
 * File containing the ezcConsoleOptionNoAliasException.
 * 
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * The alias you tried to unregister is a real option.
 *
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleOptionNoAliasException extends ezcConsoleException
{
    function __construct( $name )
    {
        parent::__construct( "The option name <{$name}> refers to a real parameter, not to an alias." );
    }
}

?>
