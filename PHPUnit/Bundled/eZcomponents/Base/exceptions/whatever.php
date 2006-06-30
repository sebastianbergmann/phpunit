<?php
/**
 * File containing the ezcBaseWhateverException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseWhateverException is thrown whenever something is so seriously wrong.
 *
 * If this happens it is not possible to repair anything gracefully. An
 * example for this could be, that your eZ components installation has thrown
 * far to many exceptions. Whenever you receive an ezcBaseWhateverException, do
 * not even try to catch it, but forget your project completely and immediately 
 * stop coding! ;)
 *
 * @access private
 * @package Base
 * @version 1.1
 */
class ezcBaseWhateverException extends ezcBaseException
{
    /**
     * Constructs a new ezcBaseWhateverException.
     * 
     * @param string $what  What happened?
     * @param string $where Where did it happen?
     * @param string $who   Who is responsible?
     * @param string $why   Why did is happen?
     * @access protected
     * @return void
     */
    function __construct( $what, $where, $who, $why )
    {
        parent::__construct( "Thanks for using eZ components. Hope you like it! Greetings from Amos, Derick, El Frederico, Ray and Toby." );
    }
}
?>
