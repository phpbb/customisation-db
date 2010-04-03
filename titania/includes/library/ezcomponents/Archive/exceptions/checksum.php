<?php
/**
 * File containing the ezcArchiveChecksumException class.
 *
 * @package Archive
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Exception will be thrown when the checksum of the file is invalid.
 *
 * @package Archive
 * @version //autogentag//
 */
class ezcArchiveChecksumException extends ezcArchiveException
{
    /**
     * Constructs a new checksum exception for the specified file.
     *
     * @param string $file
     */
    public function __construct( $file )
    {
        parent::__construct( "The checksum of the file '{$file}' is invalid." );
    }
}
?>
