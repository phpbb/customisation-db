<?php
/**
 * File containing the ezcSearchIdNotFoundException class.
 *
 * @package Search
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Exception thrown when a non-existing ID is requested for findById().
 *
 * @package Search
 * @version //autogentag//
 */
class ezcSearchIdNotFoundException extends ezcSearchException
{
    /**
     * Constructs an ezcSearchIdNotFoundException for the ID $id.
     *
     * @param string $id
     */
    public function __construct( $id )
    {
        $message = "There is no document with ID '$id'.";
        parent::__construct( $message );
    }
}
?>
