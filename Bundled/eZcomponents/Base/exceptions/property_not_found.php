<?php
/**
 * File containing the ezcPropertyNotFoundException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBasePropertyNotFoundException is thrown whenever a non existent property
 * is accessed in the Components library.
 *
 * @package Base
 * @version 1.1
 */
class ezcBasePropertyNotFoundException extends ezcBaseException
{
    /**
     * Constructs a new ezcPropertyNotFoundException for the property
     * $name.
     */
    function __construct( $name )
    {
        parent::__construct( "No such property name <{$name}>." );
    }
}
?>
