<?php
/**
 * File containing the ezcSearchQueryTools class.
 *
 * @package Search
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * The ezcSearchQueryTools class provides standard functions that don't really
 * fit anywhere else.
 *
 * @package Search
 * @version //autogentag//
 * @access private
 */
class ezcSearchQueryTools
{
    /**
     * Ascending sorting for order-by.
     */
    const ASC = 1;

    /**
     * Descending sorting for order-by.
     */
    const DESC = 2;

    /**
     * Returns all the elements in $array as one large single dimensional array.
     *
     * @param array $array
     * @return array
     */
    static public function arrayFlatten( array $array )
    {
        $flat = array();
        foreach ( $array as $arg )
        {
            switch ( gettype( $arg ) )
            {
                case 'array':
                    $flat = array_merge( $flat, $arg );
                    break;

                default:
                    $flat[] = $arg;
                    break;
            }
        }
        return $flat;
    }
}
?>
