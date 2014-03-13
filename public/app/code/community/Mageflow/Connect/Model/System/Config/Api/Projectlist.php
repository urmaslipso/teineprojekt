<?php
/**
 * ProjectList
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
 * ProjectList class
 *
 * @category   Deployment
 * @package    Application
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_System_Config_Api_Projectlist
    extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        if (($project = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_PROJECT
        ))
            && ($projectName = \Mage::app()->getStore()->getConfig(
                \Mageflow_Connect_Model_System_Config::API_PROJECT_NAME
            ))
        ) {
            return array($projectName => $projectName);
        }
        return array('' => '');
    }
}
