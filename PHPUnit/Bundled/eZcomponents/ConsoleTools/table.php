<?php
/**
 * File containing the ezcConsoleTable class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Creating tables to be printed to the console. 
 *
 * Every ezcConsoleTable object can be accessed as if it was a multidimensional,
 * numerically indexed array. The first dimension represents the rows of the 
 * table, so $table[0] gives you access to the first row of the table, which is 
 * represented by a {@link ezcConsoleTableRow} object. You can access its 
 * properties directly, using e.g. $table[0]->format. The second dimension gives 
 * you direct access to the cells of your table, like $table[0][0] accesses the 
 * first cell in the first row of your table. You can access its properties 
 * diretly here, too. This works like e.g. $table[0][0]->format. Table row and
 * cell objects are created on the fly, when you access them for the first time.
 * You can also create them as if you simply create new array elements. E.g.
 * $table[] creates a new row in the table.
 *
 * <code>
 * // Initialize the console output handler
 * $out = new ezcConsoleOutput();
 * // Define a new format "headline"
 * $out->formats->headline->color = 'red';
 * $out->formats->headline->style = array( 'bold' );
 * // Define a new format "sum"
 * $out->formats->sum->color = 'blue';
 * $out->formats->sum->style = array( 'negative' );
 * 
 * // Create a new table
 * $table = new ezcConsoleTable( $out, 60 );
 * 
 * // Create first row and in it the first cell
 * $table[0][0]->content = 'Headline 1';
 * 
 * // Create 3 more cells in row 0
 * for ( $i = 2; $i < 5; $i++ )
 * {
 *      $table[0][]->content = "Headline $i";
 * }
 * 
 * $data = array( 1, 2, 3, 4 );
 * 
 * // Create some more data in the table...
 * foreach ( $data as $value )
 * {
 *      // Create a new row each time and set it's contents to the actual value
 *      $table[][0]->content = $value;
 * }
 * 
 * // Set another border format for our headline row
 * $table[0]->borderFormat = 'headline';
 * 
 * // Set the content format for all cells of the 3rd row to "sum"
 * $table[2]->format = 'sum';
 * 
 * $table->outputTable();
 * </code>
 * 
 *
 * @see ezcConsoleOutput
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleTable implements Countable, Iterator, ArrayAccess
{
    /**
     * Automatically wrap text to fit into a column.
     * @see ezcConsoleTable::$options
     */
    const WRAP_AUTO = 1;
    /**
     * Do not wrap text. Columns will be extended to fit the largest text.
     * ATTENTION: This is risky!
     * @see ezcConsoleTable::$options
     */
    const WRAP_NONE = 2;
    /**
     * Text will be cut to fit into a column.
     * @see ezcConsoleTable::$options
     */
    const WRAP_CUT  = 3;
    
    /**
     * Align text in the default direction. 
     */
    const ALIGN_DEFAULT = -1;
    /**
     * Align text in cells to the right.
     */
    const ALIGN_LEFT   = STR_PAD_RIGHT;
    /**
     * Align text in cells to the left.
     */
    const ALIGN_RIGHT  = STR_PAD_LEFT;
    /**
     * Align text in cells to the center.
     */
    const ALIGN_CENTER = STR_PAD_BOTH;

    /**
     * The width given by settings must be used even if the data allows it smaller. 
     */
    const WIDTH_FIXED = 1;
    /**
     * The width given by settings is a maximum value, if data allows it, the table gets smaller.
     */
    const WIDTH_MAX = 2;

    /**
     * Settings for the table.
     *
     * <code>
     * array(
     *  'width' => <int>,       // Width of the table
     * );
     * </code>
     *
     * @var array(string=>string)
     */
    protected $settings = array( 
        'width' => 100,
    );

    /**
     * Options for the table.
     *
     * @var ezcConsoleTableOptions
     */
    protected $options;

    /**
     * The ezcConsoleOutput object to use.
     *
     * @var ezcConsoleOutput
     */
    protected $outputHandler;

    /**
     * Collection of the rows that are contained in the table. 
     * 
     * @var array(int=>ezcConsoleTableRow)
     */
    protected $rows;

    /**
     * Creates a new table.
     *
     * @param ezcConsoleOutput $outHandler Output handler to utilize
     * @param int $width                   Overall width of the table (chars).
     * @param array $options               Options
     *
     * @see ezcConsoleTable::$settings
     * @see ezcConsoleTable::$options
     *
     * @throws ezcBaseValueException On an invalid setting.
     */
    public function __construct( ezcConsoleOutput $outHandler, $width, $options = array() ) 
    {
        $this->outputHandler = $outHandler;
        $this->__set( 'width', $width );
        if ( $options instanceof ezcConsoleTableOptions )
        {
            $this->options = $options;
        }
        else if ( is_array( $options ) )
        {
            $this->options = new ezcConsoleTableOptions( $options );
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "array" );
        }
    }

    /**
     * Set new options.
     * This method allows you to change the options of the table.
     *
     * @param ezcConsoleTableOptions $options The options to set.
     *
     * @throws ezcBaseSettingNotFoundException
     *         If you tried to set a non-existent option value.
     * @throws ezcBaseSettingValueException
     *         If the value is not valid for the desired option.
     * @throws ezcBaseValueException
     *         If you submit neither an array nor an instance of 
     *         ezcConsoleTableOptions.
     */
    public function setOptions( $options = array() ) 
    {
        if ( is_array( $options ) ) 
        {
            $thig->options->merge( $options );
        } 
        else if ( $options instanceof ezcConsoleTableOptions ) 
        {
            $this->options = $options;
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "instance of ezcConsoleTableOptions" );
        }
    }

    /**
     * Returns the current options.
     * Returns the options currently set for this table.
     * 
     * @return ezcConsoleTableOptions The current options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the table in a string.
     * Returns the entire table as an array of printable lines. Each element of
     * the array represents a physical line of the drawn table, including all
     * borders and stuff, so you can simply print the table using
     * <code>
     * echo implode( "\n" , $table->getTable() ):
     * </code>
     * which is basically what {@link ezcConsoleTable::outputTable()} does.
     *
     * @return array An array representation of the table.
     */
    public function getTable()
    {
        return $this->generateTable();
    }

    /**
     * Output the table.
     * Prints the complete table to the console.
     *
     * @return void
     */
    public function outputTable() 
    {
        echo implode( "\n", $this->generateTable() );
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
     *         If a non numeric row ID is requested.
     */
    public function offsetExists( $offset )
    {
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        return isset( $this->rows[$offset] );
    }

    // From here only interface method implementations follow, which are not intended for direct usage

    /**
     * Returns the element with the given offset. 
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array. In case of the
     * ezcConsoleTable class this method always returns a valid row object
     * since it creates them on the fly, if a given item does not exist.
     * 
     * @param int $offset The offset to check.
     * @return ezcConsoleTableCell
     *
     * @throws ezcBaseValueException
     *         If a non numeric row ID is requested.
     */
    public function offsetGet( $offset )
    {
        if ( !isset( $offset ) )
        {
            $offset = count( $this );
            $this->rows[$offset] = new ezcConsoleTableRow();
        }
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        if ( !isset( $this->rows[$offset] ) )
        {
            $this->rows[$offset] = new ezcConsoleTableRow();
        }
        return $this->rows[$offset];
    }

    /**
     * Set the element with the given offset. 
     * This method is part of the ArrayAccess interface to allow access to the
     * data of this object as if it was an array. 
     * 
     * @param int $offset         The offset to assign an item to.
     * @param ezcConsoleTableCell The row to assign.
     * @return void
     *
     * @throws ezcBaseValueException
     *         If a non numeric row ID is requested.
     * @throws ezcBaseValueException
     *         If the provided value is not of type {@ling ezcConsoleTableRow}.
     */
    public function offsetSet( $offset, $value )
    {
        if ( !( $value instanceof ezcConsoleTableRow ) )
        {
            throw new ezcBaseValueException( 'value', $value, 'ezcConsoleTableRow' );
        }
        if ( !isset( $offset ) )
        {
            $offset = count( $this );
        }
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        $this->rows[$offset] = $value;
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
     *         If a non numeric row ID is requested.
     */
    public function offsetUnset( $offset )
    {
        if ( !is_int( $offset ) || $offset < 0 )
        {
            throw new ezcBaseValueException( 'offset', $offset, 'int >= 0' );
        }
        if ( isset( $this->rows[$offset] ) )
        {
            unset( $this->rows[$offset] );
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
        $keys = array_keys( $this->rows );
        return count( $keys ) > 0 ? ( end( $keys ) + 1 ) : 0;
    }

    /**
     * Returns the currently selected cell.
     * This method is part of the Iterator interface to allow access to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     * 
     * @return ezcConsoleTableCell The currently selected cell.
     */
    public function current()
    {
        return current( $this->rows );
    }

    /**
     * Returns the key of the currently selected cell.
     * This method is part of the Iterator interface to allow access to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     * 
     * @return int The key of the currently selected cell.
     */
    public function key()
    {
        return key( $this->rows );
    }

    /**
     * Returns the next cell and selects it or false on the last cell.
     * This method is part of the Iterator interface to allow access to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return mixed ezcConsoleTableCell if the next cell exists, or false.
     */
    public function next()
    {
        return next( $this->rows );
    }

    /**
     * Selects the very first cell and returns it.
     * This method is part of the Iterator interface to allow access to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return ezcConsoleTableCell The very first cell of this row.
     */
    public function rewind()
    {
        return reset( $this->rows );
    }

    /**
     * Returns if the current cell is valid.
     * This method is part of the Iterator interface to allow access to the 
     * cells of this row by iterating over it like an array (e.g. using
     * foreach).
     *
     * @return ezcConsoleTableCell The very first cell of this row.
     */
    public function valid()
    {
        return current( $this->rows ) !== false;
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
        switch ( $key )
        {
            case 'options':
                return $this->$key;
            case 'width':
            case 'cols':
                return $this->settings[$key];
            default:
                break;
        }
        throw new ezcBasePropertyNotFoundException( $key );
    }

    /**
     * Property write access.
     * 
     * @param string $key Name of the property.
     * @param mixed $val  The value for the property.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If a the value for the property options is not an instance of
     * @throws ezcBaseValueException
     *         If a the value for a property is out of range.
     * @return void
     */
    public function __set( $key, $val )
    {
        switch ( $key )
        {
            case 'options':
                if ( !( $val instanceof ezcConsoleTableOptions ) )
                {
                    throw new ezcBaseValueException( $key, $val, 'ezcConsoleTableOptions' );
                }
                $this->options = $val;
                return;
            case 'width':
            case 'cols':
                if ( $val < 1 )
                {
                    throw new ezcBaseValueException( $key, $val, 'int > 0' );
                }
                $this->settings[$key] = $val; 
                return;
            default:
                break;
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
            case 'options':
            case 'width':
            case 'cols':
                return true;
        }
        return false;
    }

    /**
     * Generate the complete table as an array. 
     * 
     * @return array(int=>string) The table.
     */
    private function generateTable()
    {
        $colWidth = $this->getColWidths();
        $table = array();
        $table[] = $this->generateBorder( $colWidth, $this[0]->borderFormat );
        // Rows submitted by the user
        for ( $i = 0;  $i < count( $this->rows ); $i++ )
        {
            // Auto broken rows
            foreach ( $this->breakRows( $this->rows[$i], $colWidth ) as $brkRow => $brkCells )
            {
                $table[] = $this->generateRow( $brkCells, $colWidth, $this->rows[$i] );
            }
            $afterBorderFormat = isset( $this->rows[$i + 1] ) && $this->rows[$i + 1]->borderFormat != 'default' ? $this->rows[$i + 1]->borderFormat : $this->rows[$i]->borderFormat;
            $table[] = $this->generateBorder( $colWidth, $afterBorderFormat );
        }
        return $table; 
    }

    /**
     * Generate top/bottom borders of rows. 
     * 
     * @param array(int=>int) $colWidth Array of column width.
     * @return string The Border string.
     */
    private function generateBorder( $colWidth, $format )
    {
        $border = '';
        foreach ( $colWidth as $col => $width )
        {
            $border .= $this->options->corner . str_repeat( $this->options->lineVertical, $width + ( 2 * strlen( $this->options->colPadding ) ) );
        }
        $border .= $this->options->corner;

        return $this->outputHandler->formatText( $border, $format );
    }

    /**
     * Generate a single physical row.
     * This method generates the string for a single physical table row.
     * 
     * @param array(int=>string) $cells Cells of the row.
     * @param array(int=>int) $colWidth Calculated columns widths.
     * @return string The row.
     */
    private function generateRow( $cells, $colWidth, $row )
    {
        $rowData = '';
        for ( $cell = 0; $cell < count( $colWidth ); $cell++ )
        {
            $align = $this->determineAlign( $row, $cell );
            $format = $this->determineFormat( $row, $cell );
            $borderFormat = $this->determineBorderFormat( $row );
            
            $data = isset( $cells[$cell] ) ? $cells[$cell] : '';
            $rowData .= $this->outputHandler->formatText( 
                            $this->options->lineHorizontal, 
                            $borderFormat
                        );
            $rowData .= ' ';
            $rowData .= $this->outputHandler->formatText(
                            str_pad( $data, $colWidth[$cell], ' ', $align ),
                            $format
                        );
            $rowData .= ' ';
        }
        $rowData .= $this->outputHandler->formatText( $this->options->lineHorizontal, $row->borderFormat );
        return $rowData;
    }

    /**
     * Determine the alignment of a cell.
     * Walks the inheritance path upwards to determine the alignment of a 
     * cell. Checks first, if the cell has it's own alignment (apart from 
     * ezcConsoleTable::ALIGN_DEFAULT). If not, checks the row for an 
     * alignment setting and uses the default alignment if not found.
     * 
     * @param ezcConsoleTableRow $row   The row this cell belongs to.
     * @param ezcConsoleTableCell $cellId Index of the desired cell.
     * @return int An alignement constant (ezcConsoleTable::ALIGN_*).
     */
    private function determineAlign( $row, $cellId = 0 )
    {
        return ( $row[$cellId]->align !== ezcConsoleTable::ALIGN_DEFAULT 
            ? $row[$cellId]->align
            : ( $row->align !== ezcConsoleTable::ALIGN_DEFAULT
                ? $row->align
                : ( $this->options->defaultAlign !== ezcConsoleTable::ALIGN_DEFAULT
                    ? $this->options->defaultAlign
                    : ezcConsoleTable::ALIGN_LEFT ) ) );
    }

    /**
     * Determine the format of a cells content.
     * Walks the inheritance path upwards to determine the format of a 
     * cells content. Checks first, if the cell has it's own format (apart 
     * from 'default'). If not, checks the row for a format setting and 
     * uses the default format if not found.
     * 
     * @param ezcConsoleTableRow $row   The row this cell belongs to.
     * @param ezcConsoleTableCell $cell Index of the desired cell.
     * @return string A format name.
     */
    private function determineFormat( $row, $cellId )
    {
        return ( $row[$cellId]->format != 'default'
            ? $row[$cellId]->format
            : ( $row->format !== 'default'
                ? $row->format
                : $this->options->defaultFormat ) );
    }

    /**
     * Determine the format of a rows border.
     * Walks the inheritance path upwards to determine the format of a 
     * rows border. Checks first, if the row has it's own format (apart 
     * from 'default'). If not, uses the default format.
     * 
     * @param ezcConsoleTableRow $row   The row this cell belongs to.
     * @return string A format name.
     */
    private function determineBorderFormat( $row )
    {
        return $row->borderFormat !== 'default'
            ? $row->borderFormat
            : $this->options->defaultBorderFormat;
    }

    /**
     * Returns auto broken rows from an array of cells.
     * The data provided by a user may not fit into a cell calculated by the 
     * class. In this case, the data can be automatically wrapped. The table 
     * row then spans over multiple physical console lines.
     * 
     * @param array(int=>string) $cells Array of cells in one row.
     * @param array(int=>int) $colWidth Columns widths array.
     * @return array(int=>string) Physical rows generated out of this row.
     */
    private function breakRows( $cells, $colWidth ) 
    {
        $rows = array();
        // Iterate through cells of the row
        foreach ( $colWidth as $cell => $width ) 
        {
            $data = $cells[$cell]->content;
            // Physical row id, start with 0 for each row
            $row = 0;
            // Split into multiple physical rows if manual breaks exist
            $dataLines = explode( "\n", $data );
            foreach ( $dataLines as $dataLine ) 
            {
                // Does the physical row fit?
                if ( strlen( $dataLine ) > ( $colWidth[$cell] ) )
                {
                    switch ( $this->options->colWrap )
                    {
                        case ezcConsoleTable::WRAP_AUTO:
                            $subLines = explode( "\n", wordwrap( $dataLine, $colWidth[$cell], "\n", true ) );
                            foreach ( $subLines as $lineNo => $line )
                            {
                                $rows[$row++][$cell] = $line;
                            }
                            break;
                        case ezcConsoleTable::WRAP_CUT:
                            $rows[$row++][$cell] = substr( $dataLine, 0, $colWidth[$cell] );
                            break;
                        case ezcConsoleTable::WRAP_NONE:
                        default:
                            $rows[$row++][$cell] = $dataLine;
                            break;
                    }
                }
                else
                {
                    $rows[$row++][$cell] = $dataLine;
                }
            }
        }
        return $rows;
    }

    /**
     * Determine width of each single column. 
     *
     * @return void
     */
    private function getColWidths()
    {
        if ( is_array( $this->options->colWidth ) )
        {
            return $this->options->colWidth;
        }
        // Determine number of columns:
        $colCount = 0;
        foreach ( $this->rows as $row )
        {
            $colCount = max( sizeof( $row ), $colCount );
        }
        // Subtract border and padding chars from global width
        $globalWidth = $this->width - ( $colCount * ( 2 * strlen( $this->options->colPadding ) + 1 ) ) - 1;
        // Width of a column if each is made equal
        $colNormWidth = round( $globalWidth / $colCount );
        $colMaxWidth = array();
        // Determine the longest data for each column
        foreach ( $this->rows as $row => $cells )
        {
            foreach ( $cells as $col => $cell )
            {
                $colMaxWidth[$col] = isset( $colMaxWidth[$col] ) ? max( $colMaxWidth[$col], strlen( $cell->content ) ) : strlen( $cell->content );
            }
        }
        $colWidth = array();
        $colWidthOverflow = array();
        $spareWidth = 0;
        // Make columns best fit
        foreach ( $colMaxWidth as $col => $maxWidth )
        {
            // Does the largest data of the column fit into the average size 
            // + what we have in spare from earlier columns?
            if ( $maxWidth <= ( $colNormWidth + $spareWidth ) ) 
            {
                // We fit in, make the column as large as necessary
                $colWidth[$col] = $maxWidth;
                $spareWidth += ( $colNormWidth - $maxWidth );
            }
            else
            {
                // Does not fit, use maximal possible width
                $colWidth[$col]  = $colNormWidth + $spareWidth;
                $spareWidth = 0;
                // Store overflow for second processing step
                $colWidthOverflow[$col] = $maxWidth - $colWidth[$col];
            }
        }
        // Do we have spare to give to the columns again?
        if ( $spareWidth > 0 )
        {
            // Second processing step
            if ( count( $colWidthOverflow ) > 0  )
            {
                $overflowSum = array_sum( $colWidthOverflow );
                foreach ( $colWidthOverflow as $col => $overflow );
                {
                    $colWidth[$col] += floor( $overflow / $overflowSum * $spareWidth );
                }
            }
            elseif ( $this->options->widthType === ezcConsoleTable::WIDTH_FIXED )
            {
                $widthSum = array_sum( $colWidth );
                foreach ( $colWidth as $col => $width )
                {
                    $colWidth[$col] += floor( $width / $widthSum * $spareWidth );
                }
            }
        }
        // Finally sanitize values from rounding issues, if necessary
        if ( ( $colSum = array_sum( $colWidth ) ) != $globalWidth && $this->options->widthType === ezcConsoleTable::WIDTH_FIXED )
        {
            $colWidth[count( $colWidth ) - 1] -= $colSum - $globalWidth;
        }
        return $colWidth;
    }
}
?>
