<?php
/**
 * File containing the ezcConsoleOptionTypeViolationException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * An option was submitted with an illigal type.
 * This exception can be caught using {@link ezcConsoleOptionException}.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOptionTypeViolationException extends ezcConsoleOptionException
{
    function __construct( ezcConsoleOption $option, $value )
    {
        $typeName = 'unknown';
        switch ( $option->type )
        {
            case ezcConsoleInput::TYPE_NONE:
                $typeName = 'none';
                break;
            case ezcConsoleInput::TYPE_INT:
                $typeName = 'int';
                break;
            case ezcConsoleInput::TYPE_STRING:
                $typeName = 'string';
                break;
        }
        parent::__construct( "The option <{$option->long}> expects a value of type <{$typeName}>, but received the value <{$value}>." );
    }
}
?>
