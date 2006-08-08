<?php
/**
 * File containing the ezcConsoleNoPositionStoredException.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * 'Cannot restore position, if no position has been stored before.'.
 *
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleNoPositionStoredException extends ezcConsoleException
{
    function __construct()
    {
        parent::__construct( 'Cannot restore position, if no position has been stored before.' );
    }
}

?>
