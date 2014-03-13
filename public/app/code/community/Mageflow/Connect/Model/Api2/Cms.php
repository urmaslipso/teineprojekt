<?php

/**
 * Cms
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Cms
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 *
 */
class Mageflow_Connect_Model_Api2_Cms
    extends Mageflow_Connect_Model_Api2_Abstract
{

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_Cms
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Mimic multiupdate
     *
     * @param array $filteredData
     */
    public function _multiUpdate(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log($filteredData);
        $out = array();
        foreach ($filteredData as $data) {
            $this->_update($data);
        }
        Mage::helper('mageflow_connect/log')->log('OK');
    }

}