<?php
/**
 * File containing the ezcConsoleTableRow class.
 *
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Structure representing a table row in ezcConsoleTable.
 * This class represents a row in a table object. You can access
 * the properties of the row directly, but also access the cells of 
 * the row directly, as if the object was an array (index 0..).
 *
 * <code>
 * // Create new table row
 * $row = new ezcConsoleTableRow();
 * 
 * // Set default format of the row's cells
 * $row->format = 'headline';
 * 
 * // On the fly create the cell no 0
 * $row[0]->content = 'Name';
 * // On the fly create the cell no 1
 * $row[1]->content = 'Cellphone';
 *
 * // Change a setting on cell 0
 * $row[0]->align = ezcConsoleTable::ALIGN_CENTER;
 * 
 * // Iterate through the row's cells.
 * foreach ( $row as $cell )
 * {
 *     var_dump( $cell );
 * }
 *
 * // Set the default align property for all cells in the row
 * $row->align = ezcConsoleTable::ALIGN_CENTER;
 * 
 * </code>
 *
 * This class stores the rows for the {@link ezcConsoleTable} class.
 * 
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleTableRow implements Countable, Iterator, ArrayAccess {

    /**
     * Set the format applied to the borders of this row. 
     * 
     * @see ezcConsoleOutput
     * 
     * @var string
     */
    protected $borderFormat = 'default';

    /**
     * Format applied to cell contents of cells marked with format "default" in this row.
     * 
     * @var string
     */
    protected $format = 'default';

    /**
     * Alignment applied to cells marked with ezcConsoleTable::ALIGN_DEFAULT.
     * 
     * @var mixed
     */
    protected $align = ezcConsoleTable::ALIGN_DEFAULT;

    /**
     * The cells of the row. 
     * 
     * @var array(ezcConsoleTableCell)
     */
    protected $cells = array();

    /**
     * Create a new ezcConsoleProgressbarRow. 
     * Creates a new ezcConsoleProgressbarRow. 
     *
     * This method takes any number of {@link ezcConsoleTableCell} objects as
     * parameter, which will be added as table cells to the row in their 
     * specified order.
     * 
     * @throws ezcBaseValueException
     *         If a paremeter is not of type {@link ezcConsoleTableCell}.
     */
    public function __construct()
    {
        if ( func_num_args() > 0 )
        {
            foreach ( func_get_args() as $id => $arg )
            {
                if ( !( $arg instanceof ezcConsoleTableCell ) )
                {
                    throw new ezcBaseValueException( 'Parameter'.$id, $arg, 'ezcConsoleTableCell' );
                }
                $this->cells[] = $arg;
            }
        }
    }

    /**
     * Returns if the given offset exists.
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array.
     * 
     * @param int $offset The offset to check.
     * @return bool True when the offset exists, otherwise false.
     * 
     * @throws ezcBaseValueException
     *         If a non numeric cell ID is requested.
     */
    public function offsetExists( $offset )
    {
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        return isset( $this->cells[$offset] );
    }

    /**
     * Returns the element with the given offset. 
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array. In case of the
     * ezcConsoleTableRow class this method always returns a valid cell object
     * since it creates them on the fly, if a given item does not exist.
     * 
     * @param int $offset The offset to check.
     * @return ezcConsoleTableCell
     *
     * @throws ezcBaseValueException
     *         If a non numeric cell ID is requested.
     */
    public function offsetGet( $offset )
    {
        if ( !isset( $offset ) )
        {
            $offset = count( $this );
            $this->cells[$offset] = new ezcConsoleTableCell();
        }
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        if ( !isset( $this->cells[$offset] ) )
        {
            $this->cells[$offset] = new ezcConsoleTableCell();
        }
        return $this->cells[$offset];
    }

    /**
     * Set the element with the given offset. 
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array. 
     * 
     * @param int $offset         The offset to assign an item to.
     * @param ezcConsoleTableCell The cell to assign.
     * @return void
     *
     * @throws ezcBaseValueException
     *         If a non numeric cell ID is requested.
     * @throws ezcBaseValueException
     *         If the provided value is not of type {@ling ezcConsoleTableCell}.
     */
    public function offsetSet( $offset, $value )
    {
        if ( !( $value instanceof ezcConsoleTableCell ) )
        {
            throw new ezcBaseValueException( 'value', $value, 'ezcConsoleTableCell' );
        }
        if ( !isset( $offset ) )
        {
            $offset = count( $this );
        }
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        $this->cells[$offset] = $value;
    }

    /**
     * Unset the element with the given offset. 
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array. 
     * 
     * @param int $offset The offset to unset the value for.
     * @return void
     * 
     * @throws ezcBaseValueException
     *         If a non numeric cell ID is requested.
     */
    public function offsetUnset( $offset )
    {
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        if ( isset( $this->cells[$offset] ) )
        {
            unset( $this->cells[$offset] );
        }
    }

    /**
     * Returns the number of cells in the row.
     * This method is part of the Countable interface to allow the usage of
     * PHP's count() function to check how many cells this row has.
     *
     * @return int Number of cells in this row.
     */
    public function count()
    {
        $keys = array_keys( $this->cells );
        return count( $keys ) > 0 ? ( end( $keys ) + 1 ) : 0;
    }

    /**
     * Returns the currently selected cell.
     * This method is part of the Iterator interface to allow acces to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     * 
     * @return ezcConsoleTableCell The currently selected cell.
     */
    public function current()
    {
        return current( $this->cells );
    }

    /**
     * Returns the key of the currently selected cell.
     * This method is part of the Iterator interface to allow acces to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     * 
     * @return int The key of the currently selected cell.
     */
    public function key()
    {
        return key( $this->cells );
    }

    /**
     * Returns the next cell and selects it or false on the last cell.
     * This method is part of the Iterator interface to allow acces to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return mixed ezcConsoleTableCell if the next cell exists, or false.
     */
    public function next()
    {
        return next( $this->cells );
    }

    /**
     * Selects the very first cell and returns it.
     * This method is part of the Iterator interface to allow acces to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return ezcConsoleTableCell The very first cell of this row.
     */
    public function rewind()
    {
        return reset( $this->cells );
    }

    /**
     * Returns if the current cell is valid.
     * This method is part of the Iterator interface to allow acces to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return ezcConsoleTableCell The very first cell of this row.
     */
    public function valid()
    {
        return current( $this->cells ) !== false;
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
            case 'format':
            case 'borderFormat':
                $this->$key = $val;
                return;
            case 'align':
                if ( $val !== ezcConsoleTable::ALIGN_LEFT 
                  && $val !== ezcConsoleTable::ALIGN_CENTER 
                  && $val !== ezcConsoleTable::ALIGN_RIGHT 
                  && $val !== ezcConsoleTable::ALIGN_DEFAULT 
                )
                {
                    throw new ezcBaseValueException( $key, $val, 'ezcConsoleTable::ALIGN_DEFAULT, ezcConsoleTable::ALIGN_LEFT, ezcConsoleTable::ALIGN_CENTER, ezcConsoleTable::ALIGN_RIGHT' );
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
