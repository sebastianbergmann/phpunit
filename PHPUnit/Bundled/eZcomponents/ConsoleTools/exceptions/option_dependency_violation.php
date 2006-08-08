<?php
/**
 * File containing the ezcConsoleOptionDependencyViolationException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * A dependency rule for a parameter was violated.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionDependencyViolationException extends ezcConsoleOptionException
{
    function __construct( ezcConsoleOption $dependingOption, ezcConsoleOption $dependantOption, $valueRange = null )
    {
        $message  = "The option <{$dependingOption->long}> depends on the option <{$dependantOption->long}> ";
        if ( $valueRange !== null )
        {
            $message .= " to have a value in <{$valueRange}> ";
        }
        $message .= "but this one was not submitted.";
        parent::__construct( $message );
    }
}
?>
