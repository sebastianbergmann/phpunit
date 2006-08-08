<?php
/**
 * File containing the ezcConsoleTableCell class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Representation of a table cell.
 * An object of this class represents a table cell. A cell has a certain content,
 * may apply a format to this data, align the data in the cell and so on.
 *
 * This class stores the cells for the {@link ezcConsoleTable} class.
 *
 * @see ezcConsoleTableRow
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleTableCell {

    /**
     * Text displayed in the cell. 
     * 
     * @var string
     */
    protected $content = '';

    /**
     * Format applied to the displayed text.
     * 
     * @see ezcConsoleOutput
     * 
     * @var string
     */
    protected $format = 'default';

    /**
     * Alignment of the text inside the cell.
     * Must be one of ezcConsoleTable::ALIGN_ constants.
     *
     * @see ezcConsoleTable::ALIGN_LEFT
     * @see ezcConsoleTable::ALIGN_RIGHT
     * @see ezcConsoleTable::ALIGN_CENTER
     * 
     * @var int
     */
    protected $align = ezcConsoleTable::ALIGN_DEFAULT;

    /**
     * Create a new ezcConsoleProgressbarCell. 
     * Creates a new ezcConsoleProgressbarCell. You can either submit the cell
     * data through the constructor or set them as properties.
     * 
     * @param int $verboseLevel Verbosity of the output to show.
     * @param int $autobreak    Auto wrap lines after num chars (0 = unlimited)
     * @param bool $useFormats  Whether to enable formated output
     */
    public function __construct( $content = '', $format = 'default', $align = ezcConsoleTable::ALIGN_DEFAULT )
    {
        $this->__set( 'content', $content );
        $this->__set( 'format', $format );
        $this->__set( 'align', $align );
    }

    /**
     * Property read access.
     * 
     * @param string $key Name of the property.
     * @return mixed Value of the property or null.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the the desired property is not found.
     */
    public function __get( $key )
    {
        if ( isset( $this->$key ) )
        {
            return $this->$key;
        }
    }

    /**
     * Property write access.
     * 
     * @param string $key Name of the property.
     * @param mixed $val  The value for the property.
     *
     * @throws ezcBaseValueException
     *         If a the value submitted for the align is not in the range of
     *         {@link ezcConsoleTable::ALIGN_LEFT},
     *         {@link ezcConsoleTable::ALIGN_CENTER},
     *         {@link ezcConsoleTable::ALIGN_RIGHT},
     *         {@link ezcConsoleTable::ALIGN_DEFAULT}
     *
     * @return void
     */
    public function __set( $key, $val )
    {
            
        switch ( $key )
        {
            case 'content':
            case 'format':
                $this->$key = $val;
                return;
            case 'align':
                if ( $val !== ezcConsoleTable::ALIGN_LEFT 
                  && $val !== ezcConsoleTable::ALIGN_CENTER 
                  && $val !== ezcConsoleTable::ALIGN_RIGHT 
                  && $val !== ezcConsoleTable::ALIGN_DEFAULT 
                )
                {
                    throw new ezcBaseValueException( $key,  $val, 'ezcConsoleTable::ALIGN_DEFAULT, ezcConsoleTable::ALIGN_LEFT, ezcConsoleTable::ALIGN_CENTER, ezcConsoleTable::ALIGN_RIGHT' );
                }
                $this->align = $val;
                return;
        }
        throw new ezcBasePropertyNotFoundException( $key );
    }
 
    /**
     * Property isset access.
     * 
     * @param string $key Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $key )
    {
        switch ( $key )
        {
            case 'content':
            case 'format':
            case 'align':
                return true;
            default:
                break;
        }
        return false;
    }

}

?>
