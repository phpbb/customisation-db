<?php
/**
 * File containing the ezcSearchBuildQueryException class.
 *
 * @package Search
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * This exception is thrown when the query builder can not parse the query string.
 *
 * @package Search
 * @version //autogentag//
 */
class ezcSearchBuildQueryException extends ezcSearchException
{
    /**
     * Constructs an ezcSearchBuildQueryException
     *
     * @param string $message
     */
    public function __construct( $message )
    {
        parent::__construct( $message );
    }
}
?>
