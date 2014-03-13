<?php
/**
 *
 * This class specifies types that are supported by MageFlow Extension
 *
 * Supported.php
 *
 * @author  sven
 * @created 12/20/2013 22:43
 */

class Mageflow_Connect_Model_Types_Supported extends Varien_Object
{
    /**
     * This method returns list of types that
     * MageFlow supports.
     * NB! This list may change over MFx version changes.
     *
     * @return array
     */
    public static function getSupportedTypes()
    {
        $nodeList = Mage::app()->getConfig()->getNode(
            'default/mageflow_connect/supported_types'
        )->asArray();
        $supportedTypes = array_keys($nodeList);
        return $supportedTypes;
    }
}