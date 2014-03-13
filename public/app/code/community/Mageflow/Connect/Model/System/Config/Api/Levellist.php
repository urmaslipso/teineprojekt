<?php
/**
 * ProgramList
 *
 * PHP version 5
 *
 * @category   Deployment
 * @package    Application
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */

/**
 * ProgramList class
 *
 * @category   Deployment
 * @package    Application
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_System_Config_Api_Levellist
    extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        return array(
            Zend_Log::DEBUG => 'Debug',
            Zend_Log::INFO  => 'Info',
        );
    }
}
