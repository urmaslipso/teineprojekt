<?php

require_once 'Mageflow/Connect/Module.php';
/**
 * AbstractController
 *
 * PHP version 5
 *
 * @category Mageflow
 * @package  Mageflow_Connect
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/ MageFlow Commercial Software License
 * @link     http://mageflow.com/
 */

/**
 * AbstractController
 *
 * @category Mageflow
 * @package  Mageflow_Connect
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/ MageFlow Commercial Software License
 * @link     http://mageflow.com/
 */
class Mageflow_Connect_Controller_AbstractController
    extends Mage_Adminhtml_Controller_Action
{

    /**
     * Class constuctor
     */
    public function _construct()
    {
        //include Mageflow client lib and its autoloader
        @include_once 'Mageflow/Connect/Module.php';
        $m = new \Mageflow\Connect\Module();
    }

    /**
     * @var Zend_Log
     */
    protected $_logger = null;

    /**
     * Returns logger helper instance
     *
     * @return Mageflow_Connect_Helper_Log
     */
    public function getLogger()
    {
        if (is_null($this->_logger)) {
            $this->_logger = Mage::helper('mageflow_connect/log');
        }
        return $this->_logger;
    }

}
