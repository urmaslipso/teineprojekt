<?php

/**
 * V1
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
 * V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Api2_Help_Rest_Guest_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    /**
     * Class constructor
     *
     * @return V1
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * retrieve
     *
     * @return array|Varien_Simplexml_Element
     */
    public function _retrieve()
    {
        return $this->getDetailedResourceList();
//        return 'Help index comes here';
    }

}
