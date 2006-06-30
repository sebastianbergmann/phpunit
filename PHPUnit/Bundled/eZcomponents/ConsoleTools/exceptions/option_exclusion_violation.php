<?php
/**
 * File containing the ezcConsoleOptionExclusionViolationException.
 * 
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * An exclusion rule for a parameter was violated.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleOptionExclusionViolationException extends ezcConsoleOptionException
{
    function __construct( ezcConsoleOption $excludingOption, ezcConsoleOption $excludedOption, $valueRange = null )
    {
        $message = "The option <{$excludingOption->long}> excludes the option <{$excludedOption->long}>" ;
        if ( $valueRange !== null )
        {
            $message .= "to have a value in <{$valueRange}> ";
        }
        $message .= "but this one was submitted.";
        parent::__construct( $message );
    }
}
?>
