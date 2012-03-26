<?php
/**
 * File containing the ezcArchiveException class.
 *
 * @package Archive
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * General exception class for the Archive package.
 *
 * @package Archive
 * @version //autogentag//
 */
class ezcArchiveException extends ezcBaseException
{
    /**
     * Construct a new archive exception.
     *
     * @param string $message
     */
    public function __construct( $message )
    {
        parent::__construct( $message );
    }
}
?>
