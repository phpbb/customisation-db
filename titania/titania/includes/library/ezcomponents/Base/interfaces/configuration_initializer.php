<?php
/**
 * File containing the ezcBaseConfigurationInitializer class
 *
 * @package Base
 * @version //autogen//
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * This class provides the interface that classes need to implement to act as
 * an callback initializer class to work with the delayed initialization
 * mechanism.
 *
 * @package Base
 * @version //autogen//
 */
interface ezcBaseConfigurationInitializer
{
    /**
     * Configures the given object, or returns the proper object depending on
     * the given identifier.
     *
     * In case a string identifier was given, it should return the associated
     * object, in case an object was given the method should return null.
     *
     * @param string|object $object
     * @return mixed
     */
    static public function configureObject( $object );
}
?>
