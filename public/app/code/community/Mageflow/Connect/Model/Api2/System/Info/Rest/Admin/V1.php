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
class Mageflow_Connect_Model_Api2_System_Info_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'system_info';

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_System_Info_Rest_Admin_V1
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns array with system info
     *
     * @return array
     */
    public function _retrieve()
    {
        $out = array();
        $out['resource_type'] = $this->_resourceType;
        $date = new DateTime();
        $out['updated_at'] = $date->format('c');
        $out['total_memory'] = Mage::getModel(
            'mageflow_connect/system_info_memory'
        )->getTotalMemory();
        $out['free_memory'] = Mage::getModel(
            'mageflow_connect/system_info_memory'
        )->getFreeMemory();
        $out['memory_usage'] = memory_get_usage(true);
        $out['total_disk'] = disk_total_space(dirname(__FILE__));
        $out['free_disk'] = disk_free_space(dirname(__FILE__));
        $out['cpu_cores'] = Mage::getModel('mageflow_connect/system_info_cpu')
            ->getCpuCores();
        $out['cpu_load'] = Mage::getModel('mageflow_connect/system_info_cpu')
            ->getSystemLoad();
        $out['active_sessions'] = Mage::getModel(
            'mageflow_connect/system_info_session'
        )->getNumberOfActiveSessions();
        $out['platform_info'] = php_uname();
        $out['os'] = Mage::getModel('mageflow_connect/system_info_os')
            ->getOsType();
        $out['magento_performance_history'] = Mage::getModel(
            'mageflow_connect/system_info'
        )->getPerformanceHistory();
        $out['version'] = Mage::getVersion();
        $out['mfx_version'] = Mage::app()->getConfig()->getNode(
            'modules/Mageflow_Connect/version'
        )->asArray();
        $out['cache'] = Mage::helper('mageflow_connect/system')->cacheSettings(
        );
        return $out;
    }

}
