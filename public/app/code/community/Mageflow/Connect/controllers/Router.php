<?php

/**
 * Router
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */

/**
 * Router class
 *
 * @category Deployment
 * @package  Application
 * @author   Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Controllers_Router
    extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request)
    {
        if (Mage::app()->getStore()->getConfig(
            'general/maintenance_mode/enabled'
        )
        ) {
            include_once MAGENTO_ROOT . '/errors/503.php';
            exit();
        }
        return parent::match($request);
    }
}
